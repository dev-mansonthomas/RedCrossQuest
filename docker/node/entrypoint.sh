#!/usr/bin/env bash
# Node client entrypoint - forward 127.0.0.1:8080 -> nginx:8080 so that
# gulp/server.js proxyMiddleware ('/rest' -> http://localhost:8080/) keeps
# working unchanged inside the container.
set -e

BACKEND_HOST="${BACKEND_HOST:-nginx}"
BACKEND_PORT="${BACKEND_PORT:-8080}"

# Auto-bootstrap client deps on first run. The node_modules/ and
# bower_components/ directories live on named volumes, so this only runs
# once per `make clean`. Without this, `gulp serve` (the default CMD) fails
# on a fresh clone because gulp is not resolvable in /app/client.
cd /app/client
if [[ ! -x node_modules/.bin/gulp ]]; then
    echo "[rcq-node] node_modules missing → running npm install"
    npm install --no-audit --no-fund
fi
if [[ ! -d bower_components || -z "$(ls -A bower_components 2>/dev/null)" ]]; then
    echo "[rcq-node] bower_components missing → running bower install"
    bower install --allow-root
fi

# Only start the forwarder for interactive serve targets, not for one-shot
# commands like `npm install` or `gulp build`.
case "$1" in
    gulp|npm|bower|node|sh|bash|"")
        socat TCP-LISTEN:8080,fork,reuseaddr TCP:"${BACKEND_HOST}:${BACKEND_PORT}" &
        ;;
esac

exec "$@"
