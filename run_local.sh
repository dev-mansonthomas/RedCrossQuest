#!/usr/bin/env bash
# =============================================================================
# RedCrossQuest - one-shot bootstrap of the Docker dev environment
#   * creates .env from .env.example (if missing)
#   * seeds server/src/settings.php from settings.sample.php (if missing)
#   * verifies the external `rcq_mysql` container is up (dashboard project)
#   * builds images, installs composer + npm + bower deps
#   * starts php-fpm + nginx + node-client
#   * overlays Glyphicons Pro (licensed fonts from host Google Drive)
#
# Phinx migrations are NOT run automatically: launch them manually with
#   make phinx cmd=migrate      (or status / rollback / ...)
#
# Usage:  ./run_local.sh              (full bootstrap)
#         ./run_local.sh --skip-deps  (skip composer/npm/bower install)
#         ./run_local.sh --rebuild    (force --no-cache build)
# =============================================================================
set -euo pipefail

HERE="$(cd -- "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
cd "$HERE"

SKIP_DEPS=0
REBUILD=0
for arg in "$@"; do
    case "$arg" in
        --skip-deps) SKIP_DEPS=1 ;;
        --rebuild)   REBUILD=1   ;;
        -h|--help)   sed -n '2,16p' "$0"; exit 0 ;;
        *) echo "Unknown flag: $arg" >&2; exit 1 ;;
    esac
done

say() { printf "\033[1;36m[rcq]\033[0m %s\n" "$*"; }
die() { printf "\033[1;31m[rcq]\033[0m %s\n" "$*" >&2; exit 1; }

# -----------------------------------------------------------------------------
# 0. Prerequisites
# -----------------------------------------------------------------------------
command -v docker >/dev/null || die "Docker CLI not found. Install Docker Desktop first."
docker compose version >/dev/null 2>&1 || die "Docker Compose v2 required (docker compose ...)."
docker info >/dev/null 2>&1 || die "Docker daemon not reachable. Is Docker Desktop running?"

# -----------------------------------------------------------------------------
# 1. .env bootstrap
# -----------------------------------------------------------------------------
if [[ ! -f .env ]]; then
    say "Creating .env from .env.example"
    cp .env.example .env
else
    say ".env already present — keeping existing values"
fi

# Load .env without letting shell reinterpret values that contain ';' or other
# metacharacters (e.g. MYSQL_DSN=mysql:host=rcq;port=3316;...). Docker Compose
# parses .env itself, we only need a couple of values for pre-flight checks.
env_get() {
    # $1 = key; prints raw value (without surrounding quotes) or empty
    local line
    line="$(grep -E "^[[:space:]]*$1=" .env | head -n1)" || true
    line="${line#*=}"
    line="${line%\"}"; line="${line#\"}"
    line="${line%\'}"; line="${line#\'}"
    printf '%s' "$line"
}
GOOGLE_APPLICATION_CREDENTIALS="$(env_get GOOGLE_APPLICATION_CREDENTIALS)"
HOST_PORT_API="$(env_get HOST_PORT_API)"
HOST_PORT_FRONT="$(env_get HOST_PORT_FRONT)"

# -----------------------------------------------------------------------------
# 2. GCP service-account credentials (required for Secret Manager)
# -----------------------------------------------------------------------------
# GOOGLE_APPLICATION_CREDENTIALS is set in .env to the in-container path
# (/run/secrets/…). The host's ~/.cred directory is mounted read-only on that
# mount point (see docker-compose.yml), so we verify the file exists there.
HOST_CRED_DIR="${HOME}/.cred"
HOST_CRED_FILE="${HOST_CRED_DIR}/$(basename "${GOOGLE_APPLICATION_CREDENTIALS:-}")"
if [[ ! -f "$HOST_CRED_FILE" ]]; then
    die "GCP service-account JSON not found at: $HOST_CRED_FILE
       Drop the file there (it will be mounted read-only in the php-fpm
       container at ${GOOGLE_APPLICATION_CREDENTIALS})."
fi

# -----------------------------------------------------------------------------
# 3. server/src/settings.php (never overwrite)
# -----------------------------------------------------------------------------
if [[ ! -f server/src/settings.php ]]; then
    say "Seeding server/src/settings.php from settings.sample.php"
    cp server/src/settings.sample.php server/src/settings.php
fi

# -----------------------------------------------------------------------------
# 4. External MySQL instance (rcq_mysql container, dashboard project)
# -----------------------------------------------------------------------------
# The dashboard project owns the MySQL container. We only check it is running;
# if it exists but is stopped we try to start it. We never create or destroy it.
check_rcq_mysql() {
    docker ps --format '{{.Names}}' | grep -qx 'rcq_mysql'
}

if check_rcq_mysql; then
    say "External MySQL container 'rcq_mysql' is running"
else
    if docker ps -a --format '{{.Names}}' | grep -qx 'rcq_mysql'; then
        say "Container 'rcq_mysql' exists but is stopped — starting it"
        docker start rcq_mysql >/dev/null
        sleep 2
        check_rcq_mysql || die "Failed to start 'rcq_mysql'. Start the dashboard project's MySQL stack and retry."
    else
        die "Container 'rcq_mysql' not found. Start it from the dashboard project before running this script."
    fi
fi

# -----------------------------------------------------------------------------
# 5. Build images
# -----------------------------------------------------------------------------
say "Building Docker images"
if [[ $REBUILD -eq 1 ]]; then
    docker compose build --no-cache --pull
else
    docker compose build
fi

# -----------------------------------------------------------------------------
# 6. Start application services
# -----------------------------------------------------------------------------
say "Starting php-fpm + nginx + node-client"
docker compose up -d php-fpm nginx node-client

# -----------------------------------------------------------------------------
# 7. Install deps
# -----------------------------------------------------------------------------
# The node-client entrypoint auto-runs `npm install` + `bower install` when
# the respective directories are empty, so we only need to drive composer here.
if [[ $SKIP_DEPS -eq 0 ]]; then
    say "composer install (server)"
    docker compose exec -T php-fpm composer install --no-interaction --no-progress
fi

# -----------------------------------------------------------------------------
# 7bis. Glyphicons Pro overlay (licensed assets, mandatory)
# -----------------------------------------------------------------------------
# Wait for the node-client entrypoint to finish `bower install` so that
# bower_components/bootstrap-sass exists, then overlay the paid Glyphicons Pro
# SCSS + fonts. If the licensed fonts are missing on the host,
# install_glyphicons_pro.sh aborts with an explicit error and so do we.
BOWER_DIR=/app/client/bower_components/bootstrap-sass/assets/fonts/bootstrap
GLYPHICONS_MARKER="$BOWER_DIR/glyphicons-regular.woff2"

if docker exec rcq-node test -f "$GLYPHICONS_MARKER" 2>/dev/null; then
    say "Glyphicons Pro already installed in node-client volume"
else
    say "Waiting for 'bower install' to finish in node-client"
    tries=0
    until docker exec rcq-node test -d "$BOWER_DIR" 2>/dev/null; do
        ((tries++))
        [[ $tries -gt 180 ]] && die "Timed out waiting for 'bower install' (>3 min). Inspect 'docker compose logs node-client'."
        sleep 1
    done
    say "Installing Glyphicons Pro overlay"
    bash "$HERE/client/install_glyphicons_pro.sh"
fi

# -----------------------------------------------------------------------------
# 8. Done
# -----------------------------------------------------------------------------
GREEN=$'\033[1;32m'; RESET=$'\033[0m'
printf '\n%s✓ RedCrossQuest dev stack is up%s\n\n' "$GREEN" "$RESET"
printf '  • %-24s http://localhost:%s/rest\n' 'Backend (REST API)'    "${HOST_PORT_API:-8080}"
printf '  • %-24s http://localhost:%s\n'      'Frontend (gulp serve)' "${HOST_PORT_FRONT:-3000}"
printf '  • %-24s %s\n'                        'MySQL (external)'     'rcq_mysql container on host 127.0.0.1:3316'
cat <<'EOF'

Next steps:
  make phinx cmd=status   # inspect pending migrations
  make phinx cmd=migrate  # apply them when you are ready
  make logs               # tail all services
  make shell-php          # shell inside php-fpm
  make down               # stop the stack

EOF
