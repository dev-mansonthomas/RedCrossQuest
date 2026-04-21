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

#load properties
# shellcheck source=/Users/thomasmanson/.cred/
. ~/.cred/rcq-${COUNTRY}-${ENV}.properties

#load common functions
if [[ -f common.sh ]]
then
  . common.sh
else
  . GCP/common.sh
fi
#if it does not exists, it means we're being called by ../gcp-deploy.sh (so not the same working dir), and it includes the common.sh
setProject "rcq-${COUNTRY}-${ENV}"



##############################################################
##############################################################
#                     BACK END                              #
##############################################################
##############################################################

#open proxy connection to MySQL instance
#We use 3310, so that the deployment do not conflict with existing proxy connection on port 3307 (test) & 3308 (prod)
#
# cloud-sql-proxy v2 (shipped with the gcloud SDK) uses a positional instance
# argument and `--port` instead of the legacy `-instances=...=tcp:PORT` flag.
command -v cloud-sql-proxy >/dev/null || { echo "cloud-sql-proxy not found (install it with: gcloud components install cloud-sql-proxy)"; exit 1; }

#to save money, the MySQL instance is deleted when not used
#the instance name can't be reused, so we increment a counter rcq-db-inst-fr-test-2
#
. ~/.cred/rcq-${COUNTRY}-${ENV}-db-setup.properties
CLOUD_SQL_INSTANCE="rcq-${COUNTRY}-${ENV}:europe-west1:${MYSQL_INSTANCE}"

# Reuse an already-running proxy if port 3310 is bound (previous interrupted
# deploy may have left one behind). The trap below will clean it up at exit.
if lsof -iTCP:3310 -sTCP:LISTEN -t >/dev/null 2>&1; then
  echo "cloud-sql-proxy already listening on 127.0.0.1:3310 - reusing existing process"
else
  echo "cloud-sql-proxy ${CLOUD_SQL_INSTANCE} --port 3310 &"
  cloud-sql-proxy "${CLOUD_SQL_INSTANCE}" --port 3310 &
  #read -n1 -r -p "Wait for cloud proxy to establish the connection..." key
  sleep 5
fi

# Stop cloud-sql-proxy on port 3310 when the script exits (success, failure, or
# interrupt). Ports 3307 / 3308 / others are explicitly NOT touched - only the
# deploy-scoped proxy bound to 3310 is terminated.
cleanup_cloud_sql_proxy() {
  local pid
  pid=$(lsof -iTCP:3310 -sTCP:LISTEN -t 2>/dev/null | head -1)
  if [[ -n "${pid}" ]]; then
    echo "stopping cloud-sql-proxy on port 3310 (pid=${pid})"
    kill "${pid}" 2>/dev/null || true
  fi
}
trap cleanup_cloud_sql_proxy EXIT

# Get the correct app.yaml for the env
cp ~/.cred/rcq-${COUNTRY}-${ENV}-app.yaml               server/app.yaml
#update the INSTANCE name in the file
sed -i '' -e "s/¤COUNTRY¤/${COUNTRY}/g"                 server/app.yaml
sed -i '' -e "s/¤ENV¤/${ENV}/g"                         server/app.yaml
sed -i '' -e "s/¤MYSQL_INSTANCE¤/${MYSQL_INSTANCE}/g"   server/app.yaml
sed -i '' -e "s/¤MYSQL_USER¤/${MYSQL_USER}/g"           server/app.yaml
sed -i '' -e "s/¤MYSQL_DB¤/${MYSQL_DB}/g"               server/app.yaml

#cat server/app.yaml


cp ~/.cred/phinx.yml                          server/phinx.yml
cp ~/.cred/rcq-${COUNTRY}-${ENV}-settings.php server/src/settings.php

# Phinx runs inside the `php-fpm` Docker image shipped with the repo
# (composer deps + PDO drivers already installed). The host keeps running
# cloud-sql-proxy on 127.0.0.1:3310 for dev / 3307 for test / 3308 for prod.
# Inside the container, those ports are reachable via host.docker.internal
# (mapped in docker-compose.yml via `extra_hosts: host-gateway`).
# We rewrite the copied phinx.yml accordingly, on a per-run basis.
sed -i '' -e 's/host: *127\.0\.0\.1/host: host.docker.internal/g' server/phinx.yml
sed -i '' -e 's/host: *localhost/host: host.docker.internal/g'    server/phinx.yml

command -v docker >/dev/null || { echo "Docker CLI not found on PATH"; exit 1; }
docker compose version >/dev/null 2>&1 || { echo "Docker Compose v2 required"; exit 1; }

# Ensure the image exists (first run builds it)
docker compose build php-fpm

#DB Migration
# --no-deps: don't start the local MariaDB, we target the Cloud SQL proxy.
# Composer install is needed once; it's idempotent afterwards.
docker compose run --rm --no-deps \
    -w /app/server \
    php-fpm bash -lc '
        set -e
        [[ -d vendor ]] || composer install --no-interaction --no-progress
        php vendor/bin/phinx migrate -c /app/server/phinx.yml -e rcq-'"${COUNTRY}"'-'"${ENV}"'
    '

#deployment
cd server
gcloud app deploy -q
cd -

#remove app.yaml
rm server/app.yaml

#apply GAE firewall DENY rules (idempotent, blocks known scan sources)
if [[ -f GCP/gae_firewall_rules.sh ]]; then
  bash GCP/gae_firewall_rules.sh "rcq-${COUNTRY}-${ENV}"
elif [[ -f gae_firewall_rules.sh ]]; then
  bash gae_firewall_rules.sh "rcq-${COUNTRY}-${ENV}"
fi

#restore local dev phinx.yml from the repo template
cp server/phinx-template.yml          server/phinx.yml

# DO NOT USE VARIABLE for the next line, we do want to restore the local dev version
cp ~/.cred/rcq-fr-local-settings.php  server/src/settings.php

#cloud-sql-proxy on port 3310 is stopped by the EXIT trap defined above.

#switch back to dev project (for stackdriver & storage)
gcloud config set project rcq-${COUNTRY}-dev
