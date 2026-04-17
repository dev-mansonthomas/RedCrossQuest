#!/usr/bin/env bash
# =============================================================================
# RedCrossQuest - one-shot bootstrap of the Docker dev environment
#   * creates .env from .env.example (if missing)
#   * drops a settings.php / phinx.yml if missing (never overwrites)
#   * builds images, installs composer + npm + bower deps
#   * starts the stack and runs Phinx migrations
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
        -h|--help)   sed -n '2,14p' "$0"; exit 0 ;;
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

# -----------------------------------------------------------------------------
# 1. .env bootstrap
# -----------------------------------------------------------------------------
if [[ ! -f .env ]]; then
    say "Creating .env from .env.example"
    cp .env.example .env
else
    say ".env already present — keeping existing values"
fi

# Make sure .cred directory exists (mounted read-only into php-fpm)
mkdir -p .cred

# -----------------------------------------------------------------------------
# 2. server/src/settings.php + server/phinx.yml (never overwrite)
# -----------------------------------------------------------------------------
if [[ ! -f server/src/settings.php ]]; then
    say "Seeding server/src/settings.php (Docker profile)"
    cp docker/config/settings.docker.php server/src/settings.php
fi

if [[ ! -f server/phinx.yml ]] || grep -q '%%MYSQL_ROOT_PASSWORD%%' server/phinx.yml 2>/dev/null; then
    say "Seeding server/phinx.yml (Docker profile)"
    # shellcheck disable=SC1091
    set -a; . ./.env; set +a
    sed "s|%%MYSQL_ROOT_PASSWORD%%|${MYSQL_ROOT_PASSWORD}|g" \
        docker/config/phinx.docker.yml > server/phinx.yml
fi

# -----------------------------------------------------------------------------
# 3. Build images
# -----------------------------------------------------------------------------
say "Building Docker images"
if [[ $REBUILD -eq 1 ]]; then
    docker compose build --no-cache --pull
else
    docker compose build
fi

# -----------------------------------------------------------------------------
# 4. Start core services needed for dep install
# -----------------------------------------------------------------------------
say "Starting mariadb + php-fpm + nginx + node-client"
docker compose up -d mariadb php-fpm nginx node-client

# Wait for MariaDB to become healthy (max ~60s)
say "Waiting for MariaDB to be ready"
for i in {1..30}; do
    status=$(docker inspect --format='{{.State.Health.Status}}' rcq-mariadb 2>/dev/null || echo "starting")
    [[ "$status" == "healthy" ]] && break
    sleep 2
done
[[ "$status" == "healthy" ]] || die "MariaDB did not become healthy in time"

# -----------------------------------------------------------------------------
# 5. Install deps
# -----------------------------------------------------------------------------
if [[ $SKIP_DEPS -eq 0 ]]; then
    say "composer install (server)"
    docker compose exec -T php-fpm composer install --no-interaction --no-progress

    say "npm install (client)"
    docker compose exec -T node-client npm install --no-audit --no-fund

    say "bower install (client)"
    docker compose exec -T node-client bower install --allow-root
fi

# -----------------------------------------------------------------------------
# 6. DB migrations
# -----------------------------------------------------------------------------
say "Running Phinx migrations"
docker compose exec -T php-fpm \
    php vendor/bin/phinx migrate -c /app/server/phinx.yml -e docker || \
    say "Migrations failed — inspect with: make phinx cmd=status"

# -----------------------------------------------------------------------------
# 7. Done
# -----------------------------------------------------------------------------
cat <<EOF

\033[1;32m✓ RedCrossQuest dev stack is up\033[0m

  • Backend (REST API) ... http://localhost:${HOST_PORT_API:-8080}/rest
  • Frontend (gulp serve) http://localhost:${HOST_PORT_FRONT:-3000}
  • MariaDB .............. localhost:${HOST_PORT_DB:-3306}  (user=root  pw=\$MYSQL_ROOT_PASSWORD)

Helpful commands:
  make logs           # tail all services
  make shell-php      # shell inside php-fpm
  make gulp-serve     # run gulp serve attached
  make phinx cmd=status
  make down           # stop the stack

EOF
