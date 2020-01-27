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

#load properties
. ~/.cred/rcq-${COUNTRY}-${ENV}.properties

##############################################################
##############################################################
#                     FRONT END                              #
##############################################################
##############################################################

echo "***** client build *****"
#Build the AngularJS frontend
cd client
./build.sh
cd -

echo "***** renaming index.html *****"
cd client/dist
mv index-*.html index.html

echo "***** renaming graph-display and reference to it *****"
GRAPH_FILE_NAME=$(ls graph-display-*.html)
regex="graph-display-([a-f0-9]*)\.html"

#change the reference to this ID in the app*.js file  graph-display-([a-f0-9]*)\.html -> graph.html
if [[ $GRAPH_FILE_NAME =~ $regex ]]
then
    echo "$GRAPH_FILE_NAME serial is ${BASH_REMATCH[1]}"
    SERIAL_ID=${BASH_REMATCH[1]}
    sed -i "" "s/-${SERIAL_ID}//g" scripts/app*.js
else
    echo "$GRAPH_FILE_NAME doesn't match $regex"
fi
#rename the file
mv graph-display-*.html graph-display.html


#TODO : update the path from test to prod

echo "***** updating path to dxp in graph display *****"
# Update the URL of Spotfire DXP to match the country & environment
sed -i '' "s/__country__/${COUNTRY}/g" graph-display.html
sed -i '' "s/__env__/${ENV}/g"         graph-display.html

echo "***** editing ReCaptCha key *****"
#Updating Google reCaptcha public ID
#hardcoded value that works for dev env.
sed -i '' "s/6Lckj9EUAAAAAN1apUxCdkjZRwaj1UTnYRy-I3uj/${GOOGLE_RECAPTCHA_KEY}/g"         index.html

echo "***** fixing bower libraries *****"
# TODO see how to fix this in GULP
mkdir -p bower_components/angular-i18n/ bower_components/zxcvbn/dist/    bower_components/bootstrap-sass/assets/fonts/bootstrap/
cp ../../client/bower_components/angular-i18n/angular-locale_fr-fr.js    bower_components/angular-i18n/
cp ../../client/bower_components/zxcvbn/dist/zxcvbn.js                   bower_components/zxcvbn/dist/zxcvbn.js
cp ../../client/bower_components/bootstrap-sass/assets/fonts/bootstrap/* bower_components/bootstrap-sass/assets/fonts/bootstrap/

cd -

echo "***** deploying service *****"
gcloud app deploy client/app.yaml -q #--verbosity=debug

