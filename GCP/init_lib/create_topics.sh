#!/usr/bin/env bash

#create topics
for TOPIC in 'queteur_approval_topic' 'queteur_data_updated' 'tronc_queteur' 'tronc_queteur_updated' 'ul_update'
do
  gcloud pubsub topics create ${TOPIC}
done