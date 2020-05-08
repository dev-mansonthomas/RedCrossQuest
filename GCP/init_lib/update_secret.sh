#!/usr/bin/env bash
#[13:39] thomasmanson@Thomass-MBP:~/RedCrossQuest/GCP/init_lib (secretManager)*$ ./update_secret.sh RECAPTCHA_SECRET local
#local mode
#reading property file /Users/thomasmanson/.cred/rcq-fr-dev-local.properties
#Created version [2] of the secret [local-RECAPTCHA_SECRET].



unset PROPERTIES
declare -A PROPERTIES
unset KEY
KEY=()

SECRET_NAME=$1
LOCALDEV=$2

if [[ "${SECRET_NAME}1" == "1" ]]
then
  echo "Syntax :"
  echo "update_secret.sh SECRET_NAME [local]"
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

source ./common.sh

PROPERTIES_FILE="${HOME}/.cred/rcq-${COUNTRY}-${ENV}${LOCAL_FILE}.properties"
echo "reading property file ${PROPERTIES_FILE}"
props "${PROPERTIES_FILE}"


if [[ "${PROPERTIES[${SECRET_NAME}]}1" == "1" ]]
then
  echo "Secret not found in ${PROPERTIES_FILE}"
fi

echo -n "${PROPERTIES[${SECRET_NAME}]}" | gcloud secrets versions add "${LOCAL}${SECRET_NAME}" --data-file=-
