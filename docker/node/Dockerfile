# syntax=docker/dockerfile:1.7
# -----------------------------------------------------------------------------
# RedCrossQuest - Node 10 image for the AngularJS 1.7 client
# -----------------------------------------------------------------------------
# Pinned to 10.24.1 as required by client/package.json engines field.
# Base image `node:10-buster` is still pullable from Docker Hub; we add Python 2
# because gulp-sass 3 and node-gyp transitively need it.
#
# Apple Silicon users: image is available as linux/amd64 only; run via emulation.
#   docker-compose sets `platform: linux/amd64` for this service.
# -----------------------------------------------------------------------------
ARG NODE_VERSION=10.24.1
FROM node:${NODE_VERSION}-buster

# Global tooling used by client/build.sh and client/gulp-serve.sh
# --unsafe-perm is required for gulp 3.x post-install scripts running as root
RUN apt-get update && apt-get install -y --no-install-recommends \
        python2 make g++ git socat \
    && ln -sf /usr/bin/python2 /usr/bin/python \
    && rm -rf /var/lib/apt/lists/* \
    && npm config set unsafe-perm true \
    && npm install -g --no-audit --no-fund \
        gulp@3.9.1 \
        bower@1.8.14 \
        node-gyp@5.1.1

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
