#!/usr/bin/env bash
# TODO : do the deploy outside of the source folder (copy the necessary files in a temp folder and run gcloud app deploy from there)
COUNTRY=$1
ENV=$2
TARGET=$3

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

if  [[ "${TARGET}1" == "1" ]]
then
  echo "Deploying All artifacts"
  TARGET="all"
fi

if  [[ "${TARGET}1" != "all1" ]] && [[ "${TARGET}1" != "route1" ]] && [[ "${TARGET}1" != "front1" ]] && [[ "${TARGET}1" != "back1" ]]&& [[ "${TARGET}1" != "functions1" ]]
then
  echo "'${TARGET}' the third parameter (target) is not valid. Valid values are ['route', 'front', 'back', 'functions', 'all']"
  exit 1
fi

#load common functions
. GCP/common.sh

setProject "rcq-${COUNTRY}-${ENV}"

#list current connect google account
gcloud auth list


echo "Deploying ${TARGET}"

if  [[ "${TARGET}1" == "all1" ]] || [[ "${TARGET}1" == "back1" ]]
then
  echo
  echo
  echo "##############################################################"
  echo "##############################################################"
  echo "#                     BACK END                              #"
  echo "##############################################################"
  echo "##############################################################"
  echo
  echo

  #deploy back project
  GCP/deploy_back.sh "${COUNTRY}" "${ENV}"

fi



if  [[ "${TARGET}1" == "all1" ]] || [[ "${TARGET}1" == "front1" ]]
then

  echo
  echo
  echo "##############################################################"
  echo "##############################################################"
  echo "#                     FRONT END                              #"
  echo "##############################################################"
  echo "##############################################################"
  echo
  echo

  #deploy front project
  GCP/deploy_front.sh "${COUNTRY}" "${ENV}"
fi


if  [[ "${TARGET}1" == "all1" ]] || [[ "${TARGET}1" == "functions1" ]]
then
  echo
  echo
  echo "##############################################################"
  echo "##############################################################"
  echo "#                     CLOUD FUNCTIONS                        #"
  echo "##############################################################"
  echo "##############################################################"
  echo
  echo

  #deploy back project
  GCP/deploy_cloudFunctions.sh "${COUNTRY}" "${ENV}"

fi


if  [[ "${TARGET}1" == "all1" ]] || [[ "${TARGET}1" == "route1" ]]
then
  echo
  echo
  echo "##############################################################"
  echo "##############################################################"
  echo "#                 ROUTING TABLE                              #"
  echo "##############################################################"
  echo "##############################################################"
  echo
  echo

  #deploy routing information
  gcloud app deploy GCP/dispatch.yaml -q
fi