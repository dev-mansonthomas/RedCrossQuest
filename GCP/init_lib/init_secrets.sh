#!/usr/bin/env bash
unset PROPERTIES
declare -A PROPERTIES
unset KEY
KEY=()


ENV=$1
if  [[ "${ENV}1" != "dev1" ]] && [[ "${ENV}1" != "test1" ]] && [[ "${ENV}1" != "prod1" ]]
then
  echo "'${ENV}' is not a valid environment. 'dev' (add 'local' for local secret) 'test' & 'prod' are allowed"
  exit 1
fi


LOCALDEV=$2

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

for i in "${KEY[@]}"
do
  echo "creating secrete with name '${LOCAL}$i'"
  echo -n "${PROPERTIES[$i]}" | gcloud secrets create "${LOCAL}$i" --replication-policy=automatic --data-file=-
done

#gcloud secrets create --help
