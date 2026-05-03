# syntax=docker/dockerfile:1.7
# -----------------------------------------------------------------------------
# RedCrossQuest - Node image for the AngularJS 1.8 client
# -----------------------------------------------------------------------------
# Node 22 LTS. AngularJS 1.8 is browser-only and does not constrain the
# build host. Dart Sass replaces node-sass so no native compilation step is
# needed - python and g++ are not required.
# Image is multi-arch (amd64 + arm64) so Apple Silicon runs natively.
# -----------------------------------------------------------------------------
ARG NODE_VERSION=22
FROM node:${NODE_VERSION}-bookworm-slim

# git: required by bower (resolves a few deps from GitHub).
# socat: forwards localhost:8080 -> nginx:8080 (see entrypoint.sh).
# chromium + fonts: karma-chrome-launcher needs a real browser binary to run
#   the unit-test suite headlessly (replaces the now-defunct PhantomJS).
RUN apt-get update && apt-get install -y --no-install-recommends \
        git socat ca-certificates \
        chromium fonts-liberation \
    && rm -rf /var/lib/apt/lists/* \
    && npm install -g --no-audit --no-fund \
        bower@1.8.14 \
        gulp-cli@3.0.0

# Tell karma-chrome-launcher where to find Chromium on Debian Bookworm.
ENV CHROME_BIN=/usr/bin/chromium

# gulp/server.js hardcodes `http://localhost:8080/` as the REST proxy target
# (client/gulp/server.js:36). In Docker the backend lives on `nginx:8080`.
# Rather than patching the source we forward localhost:8080 -> nginx:8080.
COPY docker/node/entrypoint.sh /usr/local/bin/rcq-entrypoint
RUN chmod +x /usr/local/bin/rcq-entrypoint
ENTRYPOINT ["/usr/local/bin/rcq-entrypoint"]

# Bower cannot run as root by default; allow it (dev container, no risk)
RUN echo '{"allow_root": true}' > /root/.bowerrc

WORKDIR /app/client

EXPOSE 3000 3001

# The default command starts gulp serve (browser-sync on :3000, UI on :3001)
CMD ["gulp", "serve"]
