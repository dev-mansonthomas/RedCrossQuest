#!/usr/bin/env bash
# =============================================================================
# Populate the local cache `client/.glyphicons-pro-cache/` with the licensed
# Glyphicons Pro font files from the developer's Google Drive.
#
# Why a local cache?
#   * The font files are licensed and MUST NOT be committed.
#   * Both the local dev stack (run_local.sh) and the GCP deploy pipeline
#     (GCP/deploy_front.sh) need them in the build container; the cache is
#     bind-mounted in via `./client:/app/client` in docker-compose.yml so that
#     a single source of truth is consumed in dev and prod (-> identical CSS).
#
# Default source path (macOS, Google Drive desktop client, post-Big-Sur):
#   $HOME/Library/CloudStorage/GoogleDrive-<email>/My Drive/03-CRF/
#       RedCrossQuest/Paid Stuff/Glyphicons Pro/glyphicons_pro_1_9_2/
#       glyphicons/web/bootstrap_example/fonts
#
# Override via env var:
#   GLYPHICONS_FONTS_DIR=/path/to/fonts ./client/sync_glyphicons_pro_cache.sh
# =============================================================================
set -euo pipefail

HERE="$(cd -- "$(dirname "${BASH_SOURCE[0]}")" &>/dev/null && pwd)"
CACHE_DIR="${HERE}/.glyphicons-pro-cache"
MARKER="glyphicons-regular.woff2"   # canonical font file used to detect cache freshness

# Default Google Drive locations to probe (in order). The CloudStorage path is
# the modern macOS layout (Big Sur+), kept first; the legacy `~/Google Drive`
# symlink is kept as a fallback for older setups.
DEFAULT_CANDIDATES=(
    "$HOME/Library/CloudStorage/GoogleDrive-mt@mansonthomas.com/My Drive/03-CRF/RedCrossQuest/Paid Stuff/Glyphicons Pro/glyphicons_pro_1_9_2/glyphicons/web/bootstrap_example/fonts"
    "$HOME/Library/CloudStorage/GoogleDrive-dev.mansonthomas@gmail.com/My Drive/03-CRF/RedCrossQuest/Paid Stuff/Glyphicons Pro/glyphicons_pro_1_9_2/glyphicons/web/bootstrap_example/fonts"
    "$HOME/Google Drive/My Drive/03-CRF/RedCrossQuest/Paid Stuff/Glyphicons Pro/glyphicons_pro_1_9_2/glyphicons/web/bootstrap_example/fonts"
)

say() { printf '\033[1;36m[glyphicons-cache]\033[0m %s\n' "$*"; }
die() { printf '\033[1;31m[glyphicons-cache]\033[0m %s\n' "$*" >&2; exit 1; }

# -----------------------------------------------------------------------------
# Already populated? -> nothing to do.
# -----------------------------------------------------------------------------
if [[ -f "$CACHE_DIR/$MARKER" ]]; then
    say "Cache already populated: $CACHE_DIR ($(ls "$CACHE_DIR" | wc -l | tr -d ' ') files)"
    exit 0
fi

# -----------------------------------------------------------------------------
# Locate source: explicit override > probed defaults
# -----------------------------------------------------------------------------
SRC=""
if [[ -n "${GLYPHICONS_FONTS_DIR:-}" ]]; then
    [[ -d "$GLYPHICONS_FONTS_DIR" ]] \
        || die "GLYPHICONS_FONTS_DIR is set but not a directory: $GLYPHICONS_FONTS_DIR"
    SRC="$GLYPHICONS_FONTS_DIR"
else
    for candidate in "${DEFAULT_CANDIDATES[@]}"; do
        if [[ -f "$candidate/$MARKER" ]]; then
            SRC="$candidate"
            break
        fi
    done
fi

if [[ -z "$SRC" ]]; then
    cat >&2 <<EOF
[glyphicons-cache] FATAL: licensed Glyphicons Pro fonts not found.

Looked in:
  \$GLYPHICONS_FONTS_DIR (unset)
$(printf '  %s\n' "${DEFAULT_CANDIDATES[@]}")

Options:
  1. Mount the project Google Drive folder so that the fonts appear at one of
     the default paths above (typical macOS dev setup).
  2. Copy the 'fonts' directory anywhere on disk and re-run with:
       GLYPHICONS_FONTS_DIR=/abs/path/to/fonts $0
  3. Drop the 5 font files directly into:
       $CACHE_DIR/

Required files: glyphicons-regular.{eot,svg,ttf,woff,woff2}
EOF
    exit 1
fi

# -----------------------------------------------------------------------------
# Populate cache
# -----------------------------------------------------------------------------
say "Source: $SRC"
say "Destination: $CACHE_DIR"
mkdir -p "$CACHE_DIR"
# Trailing `/.` copies directory contents (not the directory itself); matches
# `docker cp` semantics used downstream in install_glyphicons_pro_inside_container.sh.
cp -p "$SRC/." "$CACHE_DIR/" 2>/dev/null || cp -pR "$SRC/"* "$CACHE_DIR/"

[[ -f "$CACHE_DIR/$MARKER" ]] \
    || die "Sync completed but marker file '$MARKER' is still missing in $CACHE_DIR"

say "OK ($(ls "$CACHE_DIR" | wc -l | tr -d ' ') files cached)"
