#!/usr/bin/env bash
# =============================================================================
# Install Glyphicons Pro on top of the bootstrap-sass package installed by
# bower, inside the dockerised node-client container.
#
#  * Clones https://github.com/dev-mansonthomas/bootstrap3-glyphicons-pro to
#    get the 3 patched SCSS files (_bootstrap.scss, _variables.scss, and the
#    new _glyphicons-pro.scss partial).
#  * Copies the paid font files from the local Google Drive directory (the
#    assets are licensed and must NOT be committed).
#  * Pushes everything into the `bower-components` Docker volume via the
#    running `rcq-node` container, then restarts gulp so SCSS is recompiled.
#
# Usage:
#   ./client/install_glyphicons_pro.sh
#
# Override the font location if your Google Drive is not at the default path:
#   GLYPHICONS_FONTS_DIR="/path/to/fonts/" ./client/install_glyphicons_pro.sh
# =============================================================================
set -euo pipefail

CONTAINER="${CONTAINER:-rcq-node}"
SERVICE="${SERVICE:-node-client}"
BOWER_ROOT="/app/client/bower_components/bootstrap-sass/assets"
DEFAULT_FONTS="$HOME/Google Drive/My Drive/03-CRF/RedCrossQuest/Paid Stuff/Glyphicons Pro/glyphicons_pro_1_9_2/glyphicons/web/bootstrap_example/fonts"
FONT_LOCATION="${GLYPHICONS_FONTS_DIR:-$DEFAULT_FONTS}"

say() { printf '\033[1;36m[glyphicons]\033[0m %s\n' "$*"; }
die() { printf '\033[1;31m[glyphicons]\033[0m %s\n' "$*" >&2; exit 1; }

# -----------------------------------------------------------------------------
# Pre-flight
# -----------------------------------------------------------------------------
[[ -d "$FONT_LOCATION" ]] || die "Glyphicons Pro fonts not found at:
       $FONT_LOCATION
       Set GLYPHICONS_FONTS_DIR to the directory containing the .eot/.svg/.ttf/.woff/.woff2 files."

docker ps --format '{{.Names}}' | grep -qx "$CONTAINER" \
    || die "Container '$CONTAINER' is not running. Start the stack first (./run_local.sh)."

# -----------------------------------------------------------------------------
# 1. Fetch patched SCSS files from the glyphicons-pro overlay repo (inside
#    the container, so we don't need git on the host).
# -----------------------------------------------------------------------------
say "Cloning bootstrap3-glyphicons-pro inside '$CONTAINER'"
docker exec -u root "$CONTAINER" bash -eu -c "
    rm -rf /tmp/glyphiconspro
    mkdir -p /tmp/glyphiconspro
    cd /tmp/glyphiconspro
    git clone --depth 1 https://github.com/dev-mansonthomas/bootstrap3-glyphicons-pro.git
    SRC=/tmp/glyphiconspro/bootstrap3-glyphicons-pro/src/bootstrap-sass/assets/stylesheets
    cp \"\$SRC/_bootstrap.scss\"                $BOWER_ROOT/stylesheets/_bootstrap.scss
    cp \"\$SRC/bootstrap/_variables.scss\"      $BOWER_ROOT/stylesheets/bootstrap/_variables.scss
    cp \"\$SRC/bootstrap/_glyphicons-pro.scss\" $BOWER_ROOT/stylesheets/bootstrap/_glyphicons-pro.scss
"

# -----------------------------------------------------------------------------
# 2. Push the paid font files into the container volume
# -----------------------------------------------------------------------------
say "Copying font files from: $FONT_LOCATION"
# `docker cp "src/." "container:dst/"` replicates directory content, honouring
# spaces in the host path.
docker cp "$FONT_LOCATION/." "$CONTAINER:$BOWER_ROOT/fonts/bootstrap/"

# -----------------------------------------------------------------------------
# 3. Restart gulp so SCSS is recompiled with the glyphicons-pro partial
# -----------------------------------------------------------------------------
say "Restarting '$SERVICE' to recompile SCSS"
( cd "$(dirname "$0")/.." && docker compose restart "$SERVICE" >/dev/null )

say "Done. Reload http://localhost:3000/ (hard refresh) to pick up the new fonts."
