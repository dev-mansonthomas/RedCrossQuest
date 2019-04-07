#!/bin/bash

#
# usage : ./gcp.sh fr test stop
#         ./gcp.sh fr test start
#
# Stop or Start MySQL & All "SERVING" google app engine versions
#
#


COUNTRY=$1
ENV=$2
OPERATION=$3


if [[ "${COUNTRY}1" != "fr1" ]]
then
  echo "'${COUNTRY}' the first parameter (country) is not valid. Valid values are ['fr']"
  exit 1
fi

if [[ "${ENV}1" != "dev1" ]] && [[ "${ENV}1" != "test1" ]] && [[ "${ENV}1" != "prod1" ]]
then
  echo "'${ENV}' the second parameter (env) is not valid. Valid values are ['dev', 'test', 'prod']"
  exit 1
fi

if [[ "${OPERATION}1" != "stop1" ]] && [[ "${OPERATION}1" != "start1" ]]
then
  echo "'${OPERATION}' the third parameter (OPERATION) is not valid. Valid values are ['stop', 'start']"
  exit 1
fi


#set current project to test project
gcloud config set project rcq-${COUNTRY}-${ENV}

. ~/.cred/rcq-${COUNTRY}-${ENV}-db-setup.properties


if [[ "${OPERATION}1" = "stop1" ]]
then
  COMMAND="NEVER"
  APP_COMMAND="stop"
  echo "Stopping MySQL instance ${MYSQL_INSTANCE}"
else
  COMMAND="ALWAYS"
  APP_COMMAND="start"
  echo "Starting MySQL instance ${MYSQL_INSTANCE}"
fi


gcloud sql instances patch ${MYSQL_INSTANCE} --activation-policy ${COMMAND}

echo "Done dealing with MySQL"

VERSIONS=$(gcloud app versions list --service default --hide-no-traffic | grep -v SERVING_STATUS | cut -d " " -f3)

for VERSION in ${VERSIONS}
do
    echo "${APP_COMMAND} version ${VERSION}"
    gcloud app versions ${APP_COMMAND} --service default --quiet ${VERSION}
    echo "${APP_COMMAND} version ${VERSION} : DONE"
done