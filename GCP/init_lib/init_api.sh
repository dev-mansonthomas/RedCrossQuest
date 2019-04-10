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
#Google Cloud firestore API (to access RedQuest Firestore)
echo "gcloud services enable firestore.googleapis.com"
gcloud services enable firestore.googleapis.com