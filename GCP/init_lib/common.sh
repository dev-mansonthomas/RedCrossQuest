#!/usr/bin/env bash

props()
{
  # Read with:
  # IFS (Field Separator) =
  # -d (Record separator) newline
  # first field before separator as k (key)
  # second field after separator and reminder of record as v (value)
  while IFS='=' read -d $'\n' -r k v; do
    # Skip lines starting with sharp
    # or lines containing only space or empty lines
    [[ "$k" =~ ^([[:space:]]*|[[:space:]]*#.*)$ ]] && continue
    # Store key value into assoc array
    KEY+=($k)
    PROPERTIES[$k]="$v"
    # stdin the properties file
  done < "$1" ;
}







REDQUEST="rq"
REDCROSSQUEST="rcq"

declare -A CLOUD_FUNCTIONS=(["notifyRQOfRegistApproval"]="pubsub;queteur_approval_topic"  \
                                     ["ComputeULStats"]="http;"                           \
                                     ["ULTriggerRecompute"]="pubsub;trigger_ul_update"    \
                                     ["ztestCrossProjectFirestoCx"]="http;"               \
                                     ["findQueteurById"]="http;"                  \
                                     ["findULDetailsByToken"]="http;"             \
                                     ["getULPrefs"]="http;"                       \
                                     ["historiqueTroncQueteur"]="http;"           \
                                     ["registerQueteur"]="http;"                  \
                                     ["troncListPrepared"]="http;"                \
                                     ["troncSetDepartOrRetour"]="http;"           \
                                     ["ztestCrossProjectSQLCx"]="http;")

declare -A FUNCTIONS_PROJECT_PREFIX=(["notifyRQOfRegistApproval"]="${REDCROSSQUEST}"    \
                                     ["ComputeULStats"]="${REDCROSSQUEST}"              \
                                     ["ULTriggerRecompute"]="${REDCROSSQUEST}"          \
                                     ["ztestCrossProjectFirestoCx"]="${REDCROSSQUEST}"  \
                                     ["findQueteurById"]="${REDQUEST}"                  \
                                     ["findULDetailsByToken"]="${REDQUEST}"             \
                                     ["getULPrefs"]="${REDQUEST}"                       \
                                     ["historiqueTroncQueteur"]="${REDQUEST}"           \
                                     ["registerQueteur"]="${REDQUEST}"                  \
                                     ["troncListPrepared"]="${REDQUEST}"                \
                                     ["troncSetDepartOrRetour"]="${REDQUEST}"           \
                                     ["ztestCrossProjectSQLCx"]="${REDQUEST}")

#see https://cloud.google.com/iam/docs/understanding-roles for list of roles
#grant roles in RCQ projects to RQ & RCQ Cloud functions
#In RQ-fr-xxx projects, we grant the cloud functions to access the local project secret manager and firestore
declare -A FUNCTIONS_ROLES_RQ=(["notifyRQOfRegistApproval"]="roles/datastore.user;"       \
                               ["ComputeULStats"]="roles/datastore.user;"                 \
                               ["ULTriggerRecompute"]=""                                  \
                               ["ztestCrossProjectFirestoCx"]="roles/datastore.viewer;"   \
                               ["findQueteurById"]="roles/datastore.viewer;roles/secretmanager.secretAccessor;roles/logging.logWriter"              \
                               ["findULDetailsByToken"]="roles/secretmanager.secretAccessor;roles/logging.logWriter"                                \
                               ["getULPrefs"]="roles/datastore.viewer;roles/logging.logWriter"                                                                             \
                               ["historiqueTroncQueteur"]="roles/datastore.user;roles/secretmanager.secretAccessor;roles/logging.logWriter"         \
                               ["registerQueteur"]="roles/secretmanager.secretAccessor;roles/logging.logWriter"                                     \
                               ["troncListPrepared"]="roles/datastore.viewer;roles/secretmanager.secretAccessor;roles/logging.logWriter"            \
                               ["troncSetDepartOrRetour"]="roles/datastore.viewer;roles/secretmanager.secretAccessor;roles/logging.logWriter"       \
                               ["ztestCrossProjectSQLCx"]="roles/secretmanager.secretAccessor;roles/logging.logWriter")

#In RCQ-fr-xxx we grant the rq-fr-xxx CF to access MySQL (hosted in RCQ)
#and we grant RCQ-fr-xxx cloud function to access mysql, secret manager and pubsub
declare -A FUNCTIONS_ROLES_RCQ=(["notifyRQOfRegistApproval"]="roles/pubsub.subscriber;roles/logging.logWriter"               \
                                 ["ComputeULStats"]="roles/cloudsql.client;roles/secretmanager.secretAccessor;roles/logging.logWriter;"  \
                                 ["ULTriggerRecompute"]="roles/cloudsql.client;roles/secretmanager.secretAccessor;roles/cloudtasks.enqueuer;roles/pubsub.subscriber;roles/logging.logWriter;roles/cloudfunctions.invoker"\
                                 ["ztestCrossProjectFirestoCx"]="roles/datastore.viewer;roles/logging.logWriter"       \
                                 ["findQueteurById"]="roles/cloudsql.client;"                               \
                                 ["findULDetailsByToken"]="roles/cloudsql.client;"                          \
                                 ["getULPrefs"]="roles/datastore.viewer"                                    \
                                 ["historiqueTroncQueteur"]="roles/datastore.user;"                         \
                                 ["historiqueTroncQueteur"]="roles/cloudsql.client;"                        \
                                 ["registerQueteur"]="roles/cloudsql.client;"                               \
                                 ["troncListPrepared"]="roles/cloudsql.client;"                             \
                                 ["troncSetDepartOrRetour"]="roles/cloudsql.client;"                        \
                                 ["ztestCrossProjectSQLCx"]="roles/cloudsql.client;"                        )


declare -A FUNCTIONS_EXTRA_PARAMS=(["ULTriggerRecompute"]="")



#create one service account per function
#add roles to services accounts
#depending on the current project (rcq/rq), it will create the SA, and set the approrpiate rights
#most function  run in one project (ex: RCQ) and requires rights on the other project (RQ: firestore)
init_cloud_functions_create_service_accounts()
{
  FIRST_ARG=$1

  if  [[ "${FIRST_ARG}1" == "1" ]]
  then
    echo "Creating all cloudfunctions service accounts"
    for FUNC_NAME in "${!CLOUD_FUNCTIONS[@]}"
    do
      init_cloud_functions_create_one_service_accounts "${FUNC_NAME}"
    done
  else
    echo "Creating one cloud function service accounts within project ${PROJECT_NAME}"
     init_cloud_functions_create_one_service_accounts "${FIRST_ARG}"
  fi
}

init_cloud_functions_create_one_service_accounts()
{
  FUNC_NAME=$1
  RUNTIME_PROJECT=${FUNCTIONS_PROJECT_PREFIX[$FUNC_NAME]}

  if  [[ "${RUNTIME_PROJECT}1" == "${PROJECT_NAME}1" ]]
  then
    echo "Creating cf-${FUNC_NAME} service account running in project ${RUNTIME_PROJECT} while being in project ${PROJECT_NAME}"
    echo "gcloud iam service-accounts create cf-${FUNC_NAME,,} "
    echo "  --description=\"Service Account for the cloud function '${FUNC_NAME}'\" "
    echo "  --display-name=\"Service Account for the cloud function '${FUNC_NAME}'\" "
                                           #lowerCase otherwise : ERROR: (gcloud.iam.service-accounts.create) argument NAME: Bad value [cf-ULTriggerRecompute]: Service account name must be between 6 and 30 characters (inclusive), must begin with a lowercase letter, and consist of lowercase alphanumeric characters that can be separated by hyphens.
    gcloud iam service-accounts create "cf-${FUNC_NAME,,}" \
      --description="Service Account for the cloud function '${FUNC_NAME}'" \
      --display-name="Service Account for the cloud function '${FUNC_NAME}'"

    #read -n 1 -s
  else
    echo "${FUNC_NAME} is running in project ${RUNTIME_PROJECT} not in ${PROJECT_NAME}, nothing done"
  fi
}


init_cloud_functions_grant_roles()
{
  FIRST_ARG=$1
  
  if  [[ "${FIRST_ARG}1" == "1" ]]
  then
    echo "granting cloudfunctions service accounts roles"
    for FUNC_NAME in "${!CLOUD_FUNCTIONS[@]}"
    do
      init_one_cloud_functions_grant_roles "${FUNC_NAME}"
    done
  else
     init_one_cloud_functions_grant_roles "${FIRST_ARG}"
  fi
}


init_one_cloud_functions_grant_roles()
{
  FUNC_NAME=$1
  #get a ; separated value list of roles
  if  [[ "${PROJECT_NAME}1" == "rcq1" ]]
  then
    CLOUD_FUNCTIONS_ROLES=("${FUNCTIONS_ROLES_RCQ[$FUNC_NAME]//;/ }")
  else
    CLOUD_FUNCTIONS_ROLES=("${FUNCTIONS_ROLES_RQ[$FUNC_NAME]//;/ }")
  fi


  # shellcheck disable=SC2068
  for FUNC_ROLE in ${CLOUD_FUNCTIONS_ROLES[@]}
  do
    if  [[ "${FUNC_ROLE}1" != "1" ]]
    then
      RUNTIME_PROJECT=${FUNCTIONS_PROJECT_PREFIX[$FUNC_NAME]}
      GCLOUD_CMD="gcloud projects add-iam-policy-binding ${PROJECT_ID} --member serviceAccount:cf-${FUNC_NAME,,}@${RUNTIME_PROJECT}-${COUNTRY}-${ENV}.iam.gserviceaccount.com --role ${FUNC_ROLE}"
      echo "${GCLOUD_CMD}"
      ${GCLOUD_CMD}

      #read -n 1 -s
    fi
  done

}
