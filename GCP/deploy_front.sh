#!/usr/bin/env bash

COUNTRY=$1
ENV=$2

if [[ "${COUNTRY}1" != "fr1" ]]
then
  echo "'${COUNTRY}' the first parameter (country) is not valid. Valid values are ['fr']"
  exit 1
fi

if  [[ "${ENV}1" != "dev1" ]] && [[ "${ENV}1" != "test1" ]] && [[ "${ENV}1" != "prod1" ]]
then
  echo "'${ENV}' the second parameter (env) is not valid. Valid values are ['dev', 'test', 'prod']"
  exit 1
fi


#load common functions
if [[ -f common.sh ]]
then
  . common.sh
else
  . GCP/common.sh
fi
#if it does not exists, it means we're being called by ../gcp-deploy.sh (so not the same working dir), and it includes the common.sh
setProject "rcq-${COUNTRY}-${ENV}"

# The front build runs inside the `node-client` Docker image shipped with
# the repo (see docker/node/Dockerfile, currently Node 22 + Gulp 5).
# We no longer rely on a host-installed Node/nvm; only Docker is required.
command -v docker >/dev/null || { echo "Docker CLI not found on PATH"; exit 1; }
docker compose version >/dev/null 2>&1 || { echo "Docker Compose v2 required"; exit 1; }

#load properties
. ~/.cred/rcq-${COUNTRY}-${ENV}.properties

##############################################################
##############################################################
#                     FRONT END                              #
##############################################################
##############################################################

echo "***** client build (Docker node-client) *****"
# Ensure the node-client image exists (first run will build it)
docker compose build node-client

# Run the equivalent of the legacy client/build.sh inside the container.
# --no-deps avoids starting php-fpm/nginx just for a front build.
# --entrypoint "" bypasses the socat forwarder shipped for `gulp serve`.
docker compose run --rm --no-deps \
    --entrypoint "" \
    -w /app/client \
    node-client bash -lc '
        set -e
        rm -rf ./dist/*
        # `npm ci` is the deterministic install (fails if package.json and
        # package-lock.json are out of sync, never mutates the lockfile,
        # always starts from a clean node_modules). The previous
        # `npm install` + `npm audit fix` pair could silently bump versions
        # mid-deploy, producing a different artefact from what was tested
        # locally.
        npm ci --no-audit --no-fund
        # `gulp build` produces dist/deploy.json with the current UTC build
        # timestamp and the minified versionNotes.html (versionNotes task in
        # client/gulp/build.js). The legacy sed + buildVersionNotes.php dance
        # was removed when the build moved into the node-client image, which
        # has no PHP runtime.
        gulp build
    '

echo "***** renaming index.html *****"
cd client/dist  || exit 1
mv index-*.html index.html

echo "***** editing ReCaptCha key *****"
# Replace the dev reCAPTCHA public key (hardcoded in client/src/index.html
# for local dev convenience) with the env-specific one. The `-i.bak && rm`
# pattern is portable across BSD sed (macOS) and GNU sed (Linux runners
# such as GitHub Actions or Cloud Build); plain `sed -i ''` is BSD-only and
# `sed -i` without an arg is GNU-only.
sed -i.bak "s/6Lckj9EUAAAAAN1apUxCdkjZRwaj1UTnYRy-I3uj/${GOOGLE_RECAPTCHA_KEY}/g" index.html
rm -f index.html.bak

cd - || exit 1

echo "***** deploying service *****"
gcloud app deploy client/app.yaml -q #--verbosity=debug

