#!/usr/bin/env bash
# Node client entrypoint - forward 127.0.0.1:8080 -> nginx:8080 so that
# gulp/server.js proxyMiddleware ('/rest' -> http://localhost:8080/) keeps
# working unchanged inside the container.
set -e

BACKEND_HOST="${BACKEND_HOST:-nginx}"
BACKEND_PORT="${BACKEND_PORT:-8080}"

# Only start the forwarder for interactive serve targets, not for one-shot
# commands like `npm install` or `gulp build`.
case "$1" in
    gulp|npm|bower|node|sh|bash|"") 
        socat TCP-LISTEN:8080,fork,reuseaddr TCP:"${BACKEND_HOST}:${BACKEND_PORT}" &
        ;;
esac

exec "$@"
