#!/usr/bin/env bash

COUNTRY=$1
ENV=$2

if [ "${COUNTRY}1" != "fr1" ]
then
  echo "'${COUNTRY}' the first parameter (country) is not valid. Valid values are ['fr']"
  exit 1
fi

if [ "${ENV}1" != "test1" ] && [ "${ENV}1" != "prod1" ]
then
  echo "'${ENV}' the second parameter (env) is not valid. Valid values are ['test', 'prod']"
  exit 1
fi

#set current project to test project
gcloud config set project redcrossquest-${COUNTRY}-${ENV}

#open proxy connection to MySQL instance
#We use 3310, so that the deployment do not conflict with existing proxy connection on port 3307 (test) & 3308 (prod)
cloud_sql_proxy -instances=redcrossquest-${COUNTRY}-${ENV}:europe-west1:rcq-${COUNTRY}-${ENV}=tcp:3310 &
CLOUD_PROXY_PID=$!

#Build the AngularJS frontend
cd client
./build.sh
cd -

cd server/public_html
#remove previous version
rm -rf app assets bower_components favicon.ico graph-display.html index.html

cp -rp ../../client/dist/* .
mv index-*.html index.html
mv graph-display-*.html graph-display.html


# TODO see how to fix this in GULP
mkdir -p bower_components/angular-i18n/ bower_components/zxcvbn/dist/    bower_components/bootstrap-sass/assets/fonts/bootstrap/
cp ../../client/bower_components/angular-i18n/angular-locale_fr-fr.js    bower_components/angular-i18n/
cp ../../client/bower_components/zxcvbn/dist/zxcvbn.js                   bower_components/zxcvbn/dist/zxcvbn.js
cp ../../client/bower_components/bootstrap-sass/assets/fonts/bootstrap/* bower_components/bootstrap-sass/assets/fonts/bootstrap/

cd -

# Get the correct app.yaml for the env
cp ~/.cred/rcq-${COUNTRY}-${ENV}-app.yaml     app.yaml
cp ~/.cred/rcq-${COUNTRY}-${ENV}-settings.php server/src/settings.php

#removal of the log file
rm -f server/logs/app.log

#DB Migration
cd server
php vendor/bin/phinx migrate --configuration ~/.cred/phinx.yml -e rcq-${COUNTRY}-${ENV}
cd -

#deployment
gcloud app deploy -q

#restore default file
cp app_template.yaml                app.yaml

# DO NOT USE VARIABLE for the next line, we do want to restore the dev version
cp ~/.cred/rcq-fr-dev-settings.php  server/src/settings.php

kill -15 $CLOUD_PROXY_PID