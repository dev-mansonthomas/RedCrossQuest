#!/usr/bin/env bash

#create topics
for TOPIC in 'tronc_queteur_create' 'tronc_queteur_update' 'tronc_queteur_depart' 'tronc_queteur_return' 'tronc_queteur_updateAsAdmin' 'queteur_approval_topic' 'ul_update' 'trigger_ul_update'
do
  gcloud pubsub topics create ${TOPIC}
done




