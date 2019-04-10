#!/usr/bin/env bash
# deploy all http fonctions, or a specific function
#
# ./deploy_CloudFunctions.sh fr dev "http;registerQueteur"
# ./deploy_CloudFunctions.sh fr dev "pubsub;processNewTroncQueteur"
# ./deploy_CloudFunctions.sh fr dev all
#
#
#
#
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
#load common functions
if [[ -f common.sh ]]
then
  . common.sh
else
  . GCP/common.sh
fi
#if it does not exists, it means we're being called by ../gcp-deploy.sh (so not the same working dir), and it includes the common.sh
setProject "rcq-${COUNTRY}-${ENV}"

################################################################################################################
#  SETTINGS
################################################################################################################
REPOSITORY_ID="github_dev-mansonthomas_redcrossquestcloudfunctions"
RUNTIME="nodejs8"
REGION="europe-west1"

#list of http functions
HTTP_FUNCTIONS=("findQueteurById"          \
                "findULDetailsByToken"     \
                "registerQueteur"          \
                "z_testCrossProjectFirestoreConnectivity"\
                "z_testCrossProjectSQLConnectivity")

#list of pubsub functions
#Attention : pas d'espace entre les []
declare -A PUBSUB_FUNCTIONS=(["notifyRedQuestOfRegistrationApproval"]="queteur_approval_topic"    \
                             ["processNewTroncQueteur"]="tronc_queteur"                           \
                             ["processUpdateTroncQueteur"]="tronc_queteur_updated"                \
                             ["queteurCurrentYearAmountTimeWeigthPerYear"]="queteur_data_updated" \
                             ["ULQueteurStatsPerYear"]="ul_update"                                )

#in which project should the function be deployed : RedQuest or RedCrossQuest
#deploy the function in the correct project
REDQUEST="rq"
REDCROSSQUEST="rcq"
declare -A FUNCTIONS_PROJECT_PREFIX=(["findQueteurById"]="${REDQUEST}"                                \
                                     ["findULDetailsByToken"]="${REDQUEST}"                           \
                                     ["registerQueteur"]="${REDQUEST}"                                \
                                     ["notifyRedQuestOfRegistrationApproval"]="${REDCROSSQUEST}"      \
                                     ["processNewTroncQueteur"]="${REDCROSSQUEST}"                    \
                                     ["processUpdateTroncQueteur"]="${REDCROSSQUEST}"                 \
                                     ["queteurCurrentYearAmountTimeWeigthPerYear"]="${REDCROSSQUEST}" \
                                     ["ULQueteurStatsPerYear"]="${REDCROSSQUEST}"                     \
                                     ["z_testCrossProjectFirestoreConnectivity"]="${REDCROSSQUEST}"   \
                                     ["z_testCrossProjectSQLConnectivity"]="${REDQUEST}"              )


################################################################################################################
#  FUNCTIONS
################################################################################################################


#some functions needs env var with connection details to resources
#this function generate the yaml file and pass it to the deploy command if a shell script named after the function is present
function generateEnvFile
{
  FUNCTION_NAME="$1"
  BASH_FILE_NAME=~/.cred/functionsEnvVar/${FUNCTION_NAME}.sh
  YAML_FILE_NAME=~/.cred/functionsEnvVar/${FUNCTION_NAME}.yaml

  #test if a shell script exist that generates the env yaml file
  if [[ -f ${BASH_FILE_NAME} ]]
  then
    ENV_VAR="--env-vars-file=${YAML_FILE_NAME}"
    echo "generating env_var yaml file for ${FUNCTION_NAME}"
    #regenerate the yaml file from properties
    cd ~/.cred/functionsEnvVar/
    ${BASH_FILE_NAME} "${COUNTRY}" "${ENV}"
    cd -

    RETURN_VALUE=${ENV_VAR}
  else
    RETURN_VALUE=""
  fi
}

function deployHttpFunction
{
  FUNCTION_NAME="$1"
  #get the correct project prefix for the function
  PROJECT_ID="${FUNCTIONS_PROJECT_PREFIX[$FUNCTION_NAME]}-${COUNTRY}-${ENV}"

  SOURCE=https://source.developers.google.com/projects/${PROJECT_ID}/repos/${REPOSITORY_ID}/moveable-aliases/master/paths/${FUNCTION_NAME}

  echo
  echo "################################################################################################################"
  echo "deploying http cloud function '${FUNCTION_NAME}'"
  echo "################################################################################################################"
  echo

  generateEnvFile "${FUNCTION_NAME}"
  ENV_VAR="${RETURN_VALUE}"

  setProject ${PROJECT_ID}

  DEPLOY_CMD="gcloud functions deploy ${FUNCTION_NAME} --source ${SOURCE} --runtime ${RUNTIME} --trigger-http --region ${REGION} ${ENV_VAR}"
  echo
  echo
  echo ${DEPLOY_CMD}
  echo
  echo
  ${DEPLOY_CMD}
}

function deployPubSubFunction
{
  #take the function parameter and transform the ; delimited string into array
  PARAM_ARRAY=(${1//;/ })

  FUNCTION_NAME="${PARAM_ARRAY[0]}"
  FUNCTION_TOPIC="${PARAM_ARRAY[1]}"

  #get the correct project prefix for the function
  PROJECT_ID="${FUNCTIONS_PROJECT_PREFIX[$FUNCTION_NAME]}-${COUNTRY}-${ENV}"


  echo
  echo "################################################################################################################"
  echo "deploying PubSub cloud function '${FUNCTION_NAME}' triggered on '${FUNCTION_TOPIC}'"
  echo "################################################################################################################"
  echo

  SOURCE=https://source.developers.google.com/projects/${PROJECT_ID}/repos/${REPOSITORY_ID}/moveable-aliases/master/paths/${FUNCTION_NAME}

  generateEnvFile "${FUNCTION_NAME}"
  ENV_VAR="${RETURN_VALUE}"

  setProject ${PROJECT_ID}


  DEPLOY_CMD="gcloud functions deploy ${FUNCTION_NAME} --source ${SOURCE} --runtime ${RUNTIME} --trigger-topic ${FUNCTION_TOPIC} --region ${REGION} ${ENV_VAR}"
  echo
  echo
  echo ${DEPLOY_CMD}
  echo
  echo

  ${DEPLOY_CMD}
}

################################################################################################################
#  RUN
################################################################################################################



if  [[ "${TARGET}1" == "all1" ]]
then
  echo "Deploying all cloud functions"

  for FUNC_NAME in "${!PUBSUB_FUNCTIONS[@]}"
  do

    deployPubSubFunction "${FUNC_NAME};${PUBSUB_FUNCTIONS[$FUNC_NAME]}"

  done


  for FUNC_NAME in ${HTTP_FUNCTIONS[@]}
  do

    deployHttpFunction "${FUNC_NAME}"

  done

else

  TARGET_ARRAY=(${TARGET//;/ })
  TRIGGER_TYPE="${TARGET_ARRAY[0]}"
  FUNCTION_NAME="${TARGET_ARRAY[1]}"

  echo
  echo "################################################################################################################"
  echo "deploying PubSub cloud function '${FUNCTION_NAME}' triggertype : '${TRIGGER_TYPE}'"
  echo "################################################################################################################"
  echo


  if  [[ "${TRIGGER_TYPE}1" == "http1" ]]
  then
    deployHttpFunction "${FUNCTION_NAME}"
  else
    deployPubSubFunction "${FUNCTION_NAME};${PUBSUB_FUNCTIONS[$FUNCTION_NAME]}"
  fi
fi


#restore the default project id (rcq)
setProject "rcq-${COUNTRY}-${ENV}"








