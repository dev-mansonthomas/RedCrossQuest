#!/usr/bin/env bash
# deploy all http fonctions, or a specific function
#
# ./deploy_CloudFunctions.sh fr dev "registerQueteur"
# ./deploy_CloudFunctions.sh fr dev "processNewTroncQueteur"
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
  . init_lib/common.sh
else
  . GCP/common.sh
  . GCP/init_lib/common.sh
fi
#if it does not exists, it means we're being called by ../gcp-deploy.sh (so not the same working dir), and it includes the common.sh
setProject "rcq-${COUNTRY}-${ENV}"


#Conflict of Node version 10 is required for RedCrossQuest, and RedQuest /Cloud Functions can use 14
PATH="/usr/local/opt/node@14/bin/:$PATH"


################################################################################################################
#  SETTINGS
################################################################################################################
REPOSITORY_ID="github_dev-mansonthomas_redcrossquestcloudfunctions"
RUNTIME="nodejs12"
REGION="europe-west1"

################################################################################################################
#  CLOUD FUNCTIONS SETTINGS see GCP/init_lib/common.sh
################################################################################################################



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
    cd ~/.cred/functionsEnvVar/  || exit 1
    ${BASH_FILE_NAME} "${COUNTRY}" "${ENV}"
    cd - || exit 1

    RETURN_VALUE=${ENV_VAR}
  else
    RETURN_VALUE=""
  fi
}
#from nodejs12 deploy, the package.json and package-lock.json must be in sync
function npmInstall
{
  FUNC_NAME=$1                                           #uppercase
  local PROJECT_NAME="${FUNCTIONS_PROJECT_PREFIX[$FUNCTION_NAME]^^}"

  cd "${HOME}/RedCrossQuestCloudFunctions/${PROJECT_NAME}/${FUNC_NAME}/" || exit 1
  echo "running npm install for function ${FUNC_NAME} running in ${PROJECT_NAME}"
  npm -g install
  echo "commit and push lock file to be available for the gcloud deploy functions"
  git commit index.js common.js common_firestore.js common_firebase.js common_mysql.js package.json package-lock.json -m"Commit before deployment"
  git push
  echo "waiting 5 secs (otherwise the cloud function won't be deployed with the last code"
  sleep 5
  cd -  || exit 1
}

function deployHttpFunction
{
  FUNCTION_NAME="$1"
  #get the correct project prefix for the function
  PROJECT_NAME=${FUNCTIONS_PROJECT_PREFIX[$FUNCTION_NAME]}
  PROJECT_NAME_UPPER=$(echo ${PROJECT_NAME} | tr a-z A-Z)

  EXTRA_PARAMS=${FUNCTIONS_EXTRA_PARAMS[${FUNCTION_NAME}]}

  if [[ "${PROJECT_NAME}1" == "1" ]]
  then
    echo "${FUNCTION_NAME} is not configured in associative array FUNCTIONS_PROJECT_PREFIX "
    exit 1
  fi

  npmInstall "$FUNCTION_NAME"

  PROJECT_ID="${PROJECT_NAME}-${COUNTRY}-${ENV}"

  SOURCE=https://source.developers.google.com/projects/${PROJECT_ID}/repos/${REPOSITORY_ID}/moveable-aliases/master/paths/${PROJECT_NAME_UPPER}/${FUNCTION_NAME}

  echo
  echo "################################################################################################################"
  echo "deploying http cloud function '${FUNCTION_NAME}'"
  echo "################################################################################################################"
  echo

  generateEnvFile "${FUNCTION_NAME}"
  ENV_VAR="${RETURN_VALUE}"

  setProject "${PROJECT_ID}"

  DEPLOY_CMD="gcloud beta functions deploy ${FUNCTION_NAME} --service-account cf-${FUNCTION_NAME}@${PROJECT_ID}.iam.gserviceaccount.com --source ${SOURCE} --runtime ${RUNTIME} --trigger-http --region ${REGION} ${ENV_VAR} ${EXTRA_PARAMS} --allow-unauthenticated"
  echo
  echo
  echo "${DEPLOY_CMD}"
  echo
  echo
  ${DEPLOY_CMD}
}

function deployPubSubFunction
{
  echo $1
  #take the function parameter and transform the ; delimited string into array
  PARAM_ARRAY=(${1//;/ })

  FUNCTION_NAME="${PARAM_ARRAY[0]}"
  FUNCTION_TOPIC="${PARAM_ARRAY[1]}"

  if [[ "${FUNCTION_TOPIC}1" == "1" ]]
  then
    echo "${FUNCTION_NAME} is not configured in associative array PUBSUB_FUNCTIONS"
    exit
  fi

  npmInstall "$FUNCTION_NAME"

  #get the correct project prefix for the function
   #get the correct project prefix for the function
  PROJECT_NAME=${FUNCTIONS_PROJECT_PREFIX[${FUNCTION_NAME}]}
  PROJECT_NAME_UPPER=$(echo ${PROJECT_NAME} | tr a-z A-Z)
  PROJECT_ID="${PROJECT_NAME}-${COUNTRY}-${ENV}"

  EXTRA_PARAMS=${FUNCTIONS_EXTRA_PARAMS[${FUNCTION_NAME}]}


  echo
  echo "################################################################################################################"
  echo "deploying PubSub cloud function '${FUNCTION_NAME}' triggered on '${FUNCTION_TOPIC}'"
  echo "################################################################################################################"
  echo

  SOURCE=https://source.developers.google.com/projects/${PROJECT_ID}/repos/${REPOSITORY_ID}/moveable-aliases/master/paths/${PROJECT_NAME_UPPER}/${FUNCTION_NAME}

  generateEnvFile "${FUNCTION_NAME}"
  ENV_VAR="${RETURN_VALUE}"

  setProject "${PROJECT_ID}"

  DEPLOY_CMD="gcloud beta functions deploy ${FUNCTION_NAME} --service-account cf-${FUNCTION_NAME}@${PROJECT_ID}.iam.gserviceaccount.com --source ${SOURCE} --runtime ${RUNTIME} --trigger-topic ${FUNCTION_TOPIC} --region ${REGION} ${ENV_VAR} ${EXTRA_PARAMS} --no-allow-unauthenticated"
  echo
  echo
  echo "${DEPLOY_CMD}"
  echo
  echo

  ${DEPLOY_CMD}
}

################################################################################################################
#  RUN
################################################################################################################

echo "refreshing common.js in each cloud functions"
cd "${HOME}/RedCrossQuestCloudFunctions/" || exit 1
./copy_common.js.sh
cd - || exit 1

if  [[ "${TARGET}1" == "all1" ]]
then
  echo "Deploying all cloud functions"

  for FUNC_NAME in "${!CLOUD_FUNCTIONS[@]}"
  do
    IFS=';' read -r -a CLOUD_FUNCTIONS_DESC <<< "${CLOUD_FUNCTIONS[$FUNC_NAME]}"

    #trim ending space
    FUNCTION_TYPE="${CLOUD_FUNCTIONS_DESC[0]}"
    FUNCTION_TOPIC="${CLOUD_FUNCTIONS_DESC[1]}"

    echo "type : '$FUNCTION_TYPE'"

    if  [[ "${FUNCTION_TYPE}1" == "http1" ]]
    then
      echo "deploy http function ${FUNC_NAME}"
      deployHttpFunction "${FUNC_NAME}"
    else
      echo "deploy pubsub function ${FUNC_NAME} on topic ${FUNCTION_TOPIC}"
      deployPubSubFunction "${FUNC_NAME};${FUNCTION_TOPIC}"
    fi

  done


else

  FUNCTION_NAME="${TARGET}"
  if [[ "${FUNCTION_NAME}1" == "1" ]]
  then
    echo "Wrong syntax : function_name or trigger type is missing"
    echo
    echo '# ./deploy_CloudFunctions.sh fr dev "TRIGGER_TYPE;FUNCTION_NAME"'
    echo '# TRIGGER_TYPE: http or pubsub'
    echo
    echo '# ./deploy_CloudFunctions.sh fr dev "http;registerQueteur"'
    echo '# ./deploy_CloudFunctions.sh fr dev "pubsub;processNewTroncQueteur"'
    echo '# ./deploy_CloudFunctions.sh fr dev all'

    exit 1
  fi

  #get trigger type
  CONFIGURATION=${CLOUD_FUNCTIONS[${FUNCTION_NAME}]}
  CONFIGURATION_ARRAY=(${CONFIGURATION//;/ })
  TRIGGER_TYPE="${CONFIGURATION_ARRAY[0]}"

  echo
  echo "################################################################################################################"
  echo "deploying PubSub cloud function '${FUNCTION_NAME}' triggertype : '${TRIGGER_TYPE}'"
  echo "################################################################################################################"
  echo

  if  [[ "${TRIGGER_TYPE}1" == "http1" ]]
  then
    deployHttpFunction "${FUNCTION_NAME}"
  else
    IFS=';' read -r -a CLOUD_FUNCTIONS_DESC <<< "${CLOUD_FUNCTIONS[$FUNCTION_NAME]}"
    FUNCTION_TOPIC="${CLOUD_FUNCTIONS_DESC[1]}"
    deployPubSubFunction "${FUNCTION_NAME};${FUNCTION_TOPIC}"
  fi
fi


#restore the default project id (rcq)
setProject "rcq-${COUNTRY}-${ENV}"


cd - || exit
