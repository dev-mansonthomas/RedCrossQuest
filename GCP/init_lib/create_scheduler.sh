#!/usr/bin/env bash

gcloud beta scheduler jobs create pubsub trigger_ul_update --schedule "*/15 * * * *" --topic trigger_ul_update --message-body="{}" --time-zone="Europe/Paris" --description="trigger a cloud function that will compute stats for each UL using RCQ"