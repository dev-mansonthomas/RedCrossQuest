#!/usr/bin/env bash
# Each `gcloud app deploy` invocation below uses a yaml whose containing
# folder (client/, server/, GCP/) is the source directory; client/ and
# server/ each ship a .gcloudignore that scopes the upload to the runtime
# artefacts (dist/ and php source respectively). No upload happens from
# the repo root, so no root .gcloudignore is needed.
#
# set -euo pipefail: any failed gcloud / docker / sub-script aborts the
# whole orchestrator instead of silently continuing - the previous
# behaviour let a failed front build propagate as a successful "deploy"
# (cf. May-2025 prod front skipped without surfacing the error).
set -euo pipefail

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

if  [[ "${TARGET}1" != "all1" ]] && [[ "${TARGET}1" != "route1" ]] && [[ "${TARGET}1" != "front1" ]] && [[ "${TARGET}1" != "back1" ]] && [[ "${TARGET}1" != "fb1" ]] && [[ "${TARGET}1" != "functions1" ]]
then
  echo "'${TARGET}' the third parameter (target) is not valid. Valid values are ['route', 'front', 'back', 'fb', 'functions', 'all']"
  exit 1
fi

# 'fb' = back + front, in that order, sequentially in the same process.
# Same ordering as 'all' (DB migrations & API ship before the front that
# depends on them) but skips functions / dispatch routing. Sequential
# execution shares the gcloud global project state, so it is safe to
# rely on `setProject` at the top - the historical race condition only
# occurred when users ran two separate `./gcp-deploy.sh ... front &
# ./gcp-deploy.sh ... back` processes in parallel.

#load common functions
. GCP/common.sh

setProject "rcq-${COUNTRY}-${ENV}"

#list current connect google account
gcloud auth list


echo "Deploying ${TARGET}"

if  [[ "${TARGET}1" == "all1" ]] || [[ "${TARGET}1" == "back1" ]] || [[ "${TARGET}1" == "fb1" ]]
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



if  [[ "${TARGET}1" == "all1" ]] || [[ "${TARGET}1" == "front1" ]] || [[ "${TARGET}1" == "fb1" ]]
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
