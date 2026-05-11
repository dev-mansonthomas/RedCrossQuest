#!/usr/bin/env bash
# =============================================================================
# Install Glyphicons Pro on top of the bootstrap-sass package, inside the
# already-running `rcq-node` (node-client) container. Used by run_local.sh.
#
# Single source of truth for the overlay logic:
#   client/install_glyphicons_pro_inside_container.sh
# (the same script is invoked from GCP/deploy_front.sh in the build container,
#  guaranteeing dev = prod for the resulting CSS).
#
# Steps performed here:
#   1. Populate the host-side licensed-font cache from Google Drive
#      (client/sync_glyphicons_pro_cache.sh).
#   2. Run the in-container overlay script via docker exec on rcq-node.
#   3. Restart gulp so SCSS is recompiled with the Glyphicons Pro partial.
#
# Usage:
#   ./client/install_glyphicons_pro.sh
#
# Override the font source location if your Google Drive is elsewhere:
#   GLYPHICONS_FONTS_DIR="/path/to/fonts/" ./client/install_glyphicons_pro.sh
# =============================================================================
set -euo pipefail

HERE="$(cd -- "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
CONTAINER="${CONTAINER:-rcq-node}"
SERVICE="${SERVICE:-node-client}"

say() { printf '\033[1;36m[glyphicons]\033[0m %s\n' "$*"; }
die() { printf '\033[1;31m[glyphicons]\033[0m %s\n' "$*" >&2; exit 1; }

# -----------------------------------------------------------------------------
# 1. Populate the host-side cache (client/.glyphicons-pro-cache/) - non-versioned.
# -----------------------------------------------------------------------------
say "Ensuring local font cache is populated"
bash "$HERE/sync_glyphicons_pro_cache.sh"

# -----------------------------------------------------------------------------
# 2. Apply the overlay inside the running node-client container
# -----------------------------------------------------------------------------
docker ps --format '{{.Names}}' | grep -qx "$CONTAINER" \
    || die "Container '$CONTAINER' is not running. Start the stack first (./run_local.sh)."

say "Applying overlay inside '$CONTAINER'"
docker exec -u root "$CONTAINER" bash /app/client/install_glyphicons_pro_inside_container.sh

# -----------------------------------------------------------------------------
# 3. Restart gulp so SCSS is recompiled with the glyphicons-pro partial
# -----------------------------------------------------------------------------
say "Restarting '$SERVICE' to recompile SCSS"
( cd "$HERE/.." && docker compose restart "$SERVICE" >/dev/null )

say "Done. Reload http://localhost:3000/ (hard refresh) to pick up the new fonts."
