#!/usr/bin/env bash

#Cloud Functions
echo "gcloud services enable cloudfunctions.googleapis.com"
gcloud services enable cloudfunctions.googleapis.com
#Google Maps API
echo "gcloud services enable maps-backend.googleapis.com"
gcloud services enable maps-backend.googleapis.com
#Google GeoCoding API
echo "gcloud services enable geocoding-backend.googleapis.com"
gcloud services enable geocoding-backend.googleapis.com
#Google Cloud firestore API (to access RedQuest Firestore & RedCrossQuest firestore)
echo "gcloud services enable firestore.googleapis.com"
gcloud services enable firestore.googleapis.com
echo "gcloud services enable sqladmin.googleapis.com"
gcloud services enable sqladmin.googleapis.com

echo "gcloud services enable cloudtasks.googleapis.com"
gcloud services enable cloudtasks.googleapis.com

echo "gcloud services enable cloudbuild.googleapis.com"
gcloud services enable cloudbuild.googleapis.com
echo "gcloud services enable maps-embed-backend.googleapis.com"
gcloud services enable maps-embed-backend.googleapis.com

#secret manager
#in Google Cloud Console
#Products->Security->Secret Manager

#Secret Manager setup
echo "gcloud services enable secretmanager.googleapis.com"
gcloud services enable secretmanager.googleapis.com

GCP_PROJECT_NAME="${PROJECT_ID}"
SECRET_MANAGER_ADMIN="mt@mansonthomas.com"
SECRET_MANAGER_ADMIN_ROLE="roles/secretmanager.admin"
SECRET_MANAGER_ACCESSOR_ROLE="roles/secretmanager.secretAccessor"

#gcloud projects add-iam-policy-binding rcq-fr-dev --member "user:mt@mansonthomas.com" --role "roles/secretmanager.admin"
gcloud projects add-iam-policy-binding "${GCP_PROJECT_NAME}" \
  --member "user:${SECRET_MANAGER_ADMIN}" \
  --role "${SECRET_MANAGER_ADMIN_ROLE}"

gcloud projects add-iam-policy-binding "${GCP_PROJECT_NAME}" \
  --member "serviceAccount:${GCP_PROJECT_NAME}@appspot.gserviceaccount.com" \
  --role "${SECRET_MANAGER_ACCESSOR_ROLE}"



if [[ "${ENV}1" == "dev1" ]]
then
  echo "enable accessor role for local dev service account"
  #access for local development plateform
  gcloud projects add-iam-policy-binding "${GCP_PROJECT_NAME}" \
    --member "serviceAccount:thomas-dev@rcq-fr-dev.iam.gserviceaccount.com" \
    --role "${SECRET_MANAGER_ACCESSOR_ROLE}"
fi

init_cloud_functions_service_accounts

#Create a new secret named 'my-secret' in 'us-central1' and 'us-east1' with
#the value "s3cr3t":

#$ echo "s3cr3t" | gcloud secrets create my-secret --data-file=-

