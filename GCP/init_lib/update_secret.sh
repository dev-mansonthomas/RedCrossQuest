#!/usr/bin/env bash
#[13:39] thomasmanson@Thomass-MBP:~/RedCrossQuest/GCP/init_lib (secretManager)*$ ./update_secret.sh RECAPTCHA_SECRET local
#local mode
#reading property file /Users/thomasmanson/.cred/rcq-fr-dev-local.properties
#Created version [2] of the secret [local-RECAPTCHA_SECRET].

ENV=$1
SECRET_NAME=$2
LOCALDEV=$3

if  [[ "${ENV}1" != "dev1" ]] && [[ "${ENV}1" != "test1" ]] && [[ "${ENV}1" != "prod1" ]]
then
  echo "'${ENV}' is not a valid environment. 'dev' (add 'local' for local secret) 'test' & 'prod' are allowed"
  exit 1
fi

if [[ "${SECRET_NAME}1" == "1" ]]
then
  echo "Syntax :"
  echo "update_secret.sh ENV SECRET_NAME [local]"
  echo "ENV : dev, test, prod"
  echo "SECRET_NAME : The secret name"
  echo "'local' : add 'local' for your local dev environment (mac/pc)"
  exit 1
fi

if [[ "${LOCALDEV}1" == "local1" ]]
then
  LOCAL="local-"
  LOCAL_FILE="-local"
  echo "local mode"
fi

unset PROPERTIES
declare -A PROPERTIES
unset KEY
KEY=()

source ./common.sh

PROPERTIES_FILE="${HOME}/.cred/rcq-${COUNTRY}-${ENV}${LOCAL_FILE}.properties"
echo "reading property file ${PROPERTIES_FILE}"
props "${PROPERTIES_FILE}"


if [[ "${PROPERTIES[${SECRET_NAME}]}1" == "1" ]]
then
  echo "Secret not found in ${PROPERTIES_FILE}"
fi

echo -n "${PROPERTIES[${SECRET_NAME}]}" | gcloud secrets versions add "${LOCAL}${SECRET_NAME}" --data-file=-
