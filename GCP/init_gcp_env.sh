#!/usr/bin/env bash

#
# usage : ./init_gcp_env.sh fr test
#
# Initialize the GCP project with the basics so that other scripts can successfully be deployed
# it deploys the on time resources such as PubSub topics
#
#

COUNTRY=$1
ENV=$2

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

#load common functions
. common.sh

PROJECT_ID="rcq-${COUNTRY}-${ENV}"
setProject ${PROJECT_ID}

./init_lib/create_gae.sh
./init_lib/create_scheduler.sh
./init_lib/create_topics.sh
./init_lib/init_api.sh

#init RedQuest API as well
PROJECT_ID="rq-${COUNTRY}-${ENV}"
setProject ${PROJECT_ID}

./init_lib/init_api.sh
