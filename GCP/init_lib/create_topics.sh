#!/usr/bin/env bash

#create topics
for TOPIC in 'tronc_queteur_update' 'queteur_approval_topic' 'ul_update' 'trigger_ul_update'
do
  gcloud pubsub topics create ${TOPIC}
done

