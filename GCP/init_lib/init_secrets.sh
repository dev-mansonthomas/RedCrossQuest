#!/usr/bin/env bash
unset PROPERTIES
declare -A PROPERTIES
unset KEY
KEY=()

LOCALDEV=$1

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
