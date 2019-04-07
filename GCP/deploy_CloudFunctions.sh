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


PROJECT_ID="rcq-${COUNTRY}-${ENV}"
REPOSITORY_ID="github_dev-mansonthomas_redcrossquestcloudfunctions"
RUNTIME="nodejs8"
REGION="europe-west1"

HTTP_FUNCTIONS=("findQueteurById"          \
                "findULDetailsByToken"     \
                "registerQueteur"          )

#Attention : pas d'espace entre les []
declare -A PUBSUB_FUNCTIONS=(["notifyRedQuestOfRegistrationApproval"]="queteur_approval_topic"      \
                             ["processNewTroncQueteur"]="tronc_queteur"                             \
                             ["processUpdateTroncQueteur"]="tronc_queteur_updated"                  \
                             ["queteurCurrentYearAmountTimeWeigthPerYear"]="queteur_data_updated"   \
                             ["ULQueteurStatsPerYear"]="ul_update")


function deployHttpFunction
{
  FUNCTION_NAME="$1"
  SOURCE=https://source.developers.google.com/projects/${PROJECT_ID}/repos/${REPOSITORY_ID}/moveable-aliases/master/paths/${FUNCTION_NAME}

  echo
  echo "################################################################################################################"
  echo "deploying http cloud function '${FUNCTION_NAME}'"
  echo "################################################################################################################"
  echo

  gcloud functions deploy ${FUNCTION_NAME} \
    --source        ${SOURCE}   \
    --runtime       ${RUNTIME}  \
    --trigger-http              \
    --region        ${REGION}
}

function deployPubSubFunction
{
  PARAM_ARRAY=(${1//;/ })

  FUNCTION_NAME="${PARAM_ARRAY[0]}"
  FUNCTION_TOPIC="${PARAM_ARRAY[1]}"

  echo
  echo "################################################################################################################"
  echo "deploying PubSub cloud function '${FUNCTION_NAME}' triggered on '${FUNCTION_TOPIC}'"
  echo "################################################################################################################"
  echo

  SOURCE=https://source.developers.google.com/projects/${PROJECT_ID}/repos/${REPOSITORY_ID}/moveable-aliases/master/paths/${FUNCTION_NAME}

  gcloud functions deploy ${FUNCTION_NAME}  \
    --source        ${SOURCE}               \
    --runtime       ${RUNTIME}              \
    --trigger-topic ${FUNCTION_TOPIC}       \
    --region        ${REGION}
}





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









