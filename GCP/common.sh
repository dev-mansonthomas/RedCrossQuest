#!/usr/bin/env bash


function setProject
{
  CURRENT_PROJECT=$(gcloud config get-value project)
  TARGET_PROJECT="$1"

  if [[ ${CURRENT_PROJECT} != "${TARGET_PROJECT}" ]]
  then

    echo "GCP Project was '${CURRENT_PROJECT}', updating it to '${TARGET_PROJECT}'"
    #set current project to target project"
    gcloud config set project ${TARGET_PROJECT}

  fi
}