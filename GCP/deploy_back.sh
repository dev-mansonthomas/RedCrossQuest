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


#Conflict of Node version 10 is required for RedCrossQuest, and RedQuest /Cloud Functions can use 14
#PATH="/usr/local/opt/node@14/bin/:$PATH"
. $(brew --prefix nvm)/nvm.sh
nvm use v10.24.1


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
#cloud_sql_proxy -instances=rcq-${COUNTRY}-${ENV}:europe-west1:rcq-${COUNTRY}-${ENV}=tcp:3310 &

#to save money, the MySQL instance is deleted when not used
#the instance name can't be reused, so we increment a counter rcq-db-inst-fr-test-2
#
. ~/.cred/rcq-${COUNTRY}-${ENV}-db-setup.properties
echo "cloud_sql_proxy -instances=rcq-${COUNTRY}-${ENV}:europe-west1:${MYSQL_INSTANCE}=tcp:3310 &"
cloud_sql_proxy -instances=rcq-${COUNTRY}-${ENV}:europe-west1:${MYSQL_INSTANCE}=tcp:3310 &
CLOUD_PROXY_PID=$!

#read -n1 -r -p "Wait for cloud proxy to establish the connection..." key
sleep 5

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


#DB Migration
cd server
php vendor/bin/phinx migrate -e rcq-${COUNTRY}-${ENV}
cd -

#deployment
cd server
gcloud app deploy -q
cd -

#remove app.yaml
rm server/app.yaml

#restore default file
cp server/phinx-template.yml        server/phinx.yml

# DO NOT USE VARIABLE for the next line, we do want to restore the local dev version
cp ~/.cred/rcq-fr-local-settings.php  server/src/settings.php

kill -15 $CLOUD_PROXY_PID

#switch back to dev project (for stackdriver & storage)
gcloud config set project rcq-${COUNTRY}-dev
