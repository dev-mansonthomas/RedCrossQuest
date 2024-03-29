#!/usr/bin/env bash
#[13:39] thomasmanson@Thomass-MBP:~/RedCrossQuest/GCP/init_lib (secretManager)*$ ./update_secret.sh RECAPTCHA_SECRET local
#local mode
#reading property file /Users/thomasmanson/.cred/rcq-fr-dev-local.properties
#Created version [2] of the secret [local-RECAPTCHA_SECRET].

COUNTRY=$1
ENV=$2
SECRET_NAME=$3
LOCALDEV=$4
CREATE=$5

if [[ "${COUNTRY}1" != "fr1" ]]
then
  echo "'${COUNTRY}' the first parameter (country) is not valid. Valid values are ['fr']"
  exit 1
fi

if  [[ "${ENV}1" != "dev1" ]] && [[ "${ENV}1" != "test1" ]] && [[ "${ENV}1" != "prod1" ]]
then
  echo "'${ENV}' is not a valid environment. 'dev' (add 'local' for local secret) 'test' & 'prod' are allowed"
  exit 1
fi

if [[ "${SECRET_NAME}1" == "1" ]]
then
  echo "Syntax :"
  echo "update_secret.sh COUNTRY ENV SECRET_NAME [local/net] [create]"
  echo "COUNTRY : fr, be"
  echo "ENV : dev, test, prod"
  echo "SECRET_NAME : The secret name"
  echo "'local' : add 'local' for your local dev environment (mac/pc)"
  echo "example:  ./update_secret.sh dev SLACK-TOKEN local create"
  echo "example:  ./update_secret.sh dev SLACK-TOKEN net create"
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
source ../common.sh

setProject "rcq-${COUNTRY}-${ENV}"

PROPERTIES_FILE="${HOME}/.cred/rcq-${COUNTRY}-${ENV}${LOCAL_FILE}.properties"
echo "reading property file ${PROPERTIES_FILE}"
props "${PROPERTIES_FILE}"


if [[ "${PROPERTIES[${SECRET_NAME}]}1" == "1" ]]
then
  echo "Secret not found in ${PROPERTIES_FILE}"
fi

if [[ "${CREATE}1" == "create1" ]]
then
  echo -n "${PROPERTIES[${SECRET_NAME}]}" | gcloud secrets create "${LOCAL}${SECRET_NAME}"  --replication-policy=user-managed --locations=europe-central2,europe-north1,europe-west1,europe-west2,europe-west3,europe-west4,europe-west6  --data-file=-
else
  echo -n "${PROPERTIES[${SECRET_NAME}]}" | gcloud secrets versions add "${LOCAL}${SECRET_NAME}"  --data-file=-
fi


