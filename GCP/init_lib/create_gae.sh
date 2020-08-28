#!/usr/bin/env bash

#create the GAE env
gcloud app create --region=europe-west


gcloud tasks queues create compute-stats-on-mysql \
    --max-dispatches-per-second=10 \
    --max-concurrent-dispatches=15 \
    --max-attempts=2 \
    --min-backoff=1s


#gcloud iam service-accounts create ct-compute-stats-on-mysql --description="Service Account for the cloud task compute-stats-on-mysql" --display-name="Service Account for the cloud task compute-stats-on-mysql"
#gcloud projects add-iam-policy-binding ${PROJECT_ID} --member serviceAccount:ct-compute-stats-on-mysql@${PROJECT_ID}.iam.gserviceaccount.com --role "roles/cloudfunctions.invoker"


