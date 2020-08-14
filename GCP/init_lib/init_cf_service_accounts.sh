#!/usr/bin/env bash

COUNTRY=$1
ENV=$2
CREATE_OR_UPDATE=$3


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

if [[ "${CREATE_OR_UPDATE}1" != "create1" ]] && [[ "${CREATE_OR_UPDATE}1" != "update1" ]]
then
  echo "'${CREATE_OR_UPDATE}' the second parameter (CREATE_OR_UPDATE) is not valid. Valid values are ['create', 'update']. Create: create and set roles. Updates :  set roles only"
  exit 1
fi


. ./common.sh
. ../common.sh

if [[ "${CREATE_OR_UPDATE}1" == "create1" ]]
then

  #create service accounts and then grant roles
  PROJECT_NAME="rcq"
  PROJECT_ID="${PROJECT_NAME}-${COUNTRY}-${ENV}"
  setProject "${PROJECT_ID}"

  init_cloud_functions_create_service_accounts

  PROJECT_NAME="rq"
  PROJECT_ID="${PROJECT_NAME}-${COUNTRY}-${ENV}"
  setProject "${PROJECT_ID}"

  init_cloud_functions_create_service_accounts

else
  #set the correct project
  PROJECT_NAME="rq"
  PROJECT_ID="${PROJECT_NAME}-${COUNTRY}-${ENV}"
  setProject "${PROJECT_ID}"

fi


init_cloud_functions_grant_roles

PROJECT_NAME="rcq"
PROJECT_ID="${PROJECT_NAME}-${COUNTRY}-${ENV}"
setProject "${PROJECT_ID}"

init_cloud_functions_grant_roles
