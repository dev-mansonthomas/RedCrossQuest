#!/usr/bin/env bash
# =============================================================================
# Apply the Glyphicons Pro overlay on top of the bootstrap-sass package
# inside the node-client container. Single source of truth used identically
# by both:
#   * run_local.sh           (dev: docker exec rcq-node bash <this script>)
#   * GCP/deploy_front.sh    (prod: appended to the docker compose run -lc)
#
# Pre-condition: client/.glyphicons-pro-cache/ is populated on the host (see
# client/sync_glyphicons_pro_cache.sh) and bind-mounted at
# /app/client/.glyphicons-pro-cache/ inside the container.
#
# Steps:
#   1. Clone the SCSS overlay repo (3 patched files for bootstrap-sass).
#   2. Copy the patched SCSS files into node_modules/bootstrap-sass.
#   3. Copy the cached licensed fonts into node_modules/bootstrap-sass.
# =============================================================================
set -euo pipefail

BOOTSTRAP_ROOT="/app/client/node_modules/bootstrap-sass/assets"
CACHE_DIR="/app/client/.glyphicons-pro-cache"
OVERLAY_REPO="https://github.com/dev-mansonthomas/bootstrap3-glyphicons-pro.git"
WORKDIR="/tmp/glyphicons-pro-overlay"

say() { printf '\033[1;36m[glyphicons-overlay]\033[0m %s\n' "$*"; }
die() { printf '\033[1;31m[glyphicons-overlay]\033[0m %s\n' "$*" >&2; exit 1; }

# -----------------------------------------------------------------------------
# Pre-flight
# -----------------------------------------------------------------------------
[[ -d "$BOOTSTRAP_ROOT" ]] \
    || die "bootstrap-sass not found at $BOOTSTRAP_ROOT (npm install not run yet?)"

[[ -f "$CACHE_DIR/glyphicons-regular.woff2" ]] \
    || die "Local font cache is empty: $CACHE_DIR
       Populate it on the host with: bash client/sync_glyphicons_pro_cache.sh"

# -----------------------------------------------------------------------------
# 1. Fetch the SCSS overlay (3 patched files)
# -----------------------------------------------------------------------------
say "Cloning SCSS overlay: $OVERLAY_REPO"
rm -rf "$WORKDIR"
git clone --depth 1 "$OVERLAY_REPO" "$WORKDIR" >/dev/null 2>&1 \
    || die "git clone failed: $OVERLAY_REPO"

SRC="$WORKDIR/src/bootstrap-sass/assets/stylesheets"
[[ -f "$SRC/_bootstrap.scss" ]] \
    || die "Overlay repo layout unexpected, missing $SRC/_bootstrap.scss"

# -----------------------------------------------------------------------------
# 2. Install patched SCSS files
# -----------------------------------------------------------------------------
say "Installing patched SCSS files into bootstrap-sass"
cp "$SRC/_bootstrap.scss"                "$BOOTSTRAP_ROOT/stylesheets/_bootstrap.scss"
cp "$SRC/bootstrap/_variables.scss"      "$BOOTSTRAP_ROOT/stylesheets/bootstrap/_variables.scss"
cp "$SRC/bootstrap/_glyphicons-pro.scss" "$BOOTSTRAP_ROOT/stylesheets/bootstrap/_glyphicons-pro.scss"

# -----------------------------------------------------------------------------
# 3. Install licensed font files from the host-mounted cache
# -----------------------------------------------------------------------------
DEST_FONTS="$BOOTSTRAP_ROOT/fonts/bootstrap"
say "Installing licensed fonts from cache -> $DEST_FONTS"
mkdir -p "$DEST_FONTS"
cp "$CACHE_DIR/"glyphicons-regular.* "$DEST_FONTS/"

# -----------------------------------------------------------------------------
# 4. Cleanup
# -----------------------------------------------------------------------------
rm -rf "$WORKDIR"

say "Done."
