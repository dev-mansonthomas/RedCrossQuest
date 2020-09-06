#!/usr/bin/env bash

#Cloud Functions
echo "gcloud services enable cloudfunctions.googleapis.com"
gcloud services enable cloudfunctions.googleapis.com
#Google Maps API
echo "gcloud services enable maps-embed-backend.googleapis.com"
gcloud services enable maps-embed-backend.googleapis.com
#Google Cloud firestore API (to access RedQuest Firestore)
echo "gcloud services enable firestore.googleapis.com"
gcloud services enable firestore.googleapis.com

echo "gcloud services enable sqladmin.googleapis.com"
gcloud services enable sqladmin.googleapis.com


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


init_cloud_functions_service_accounts
