#!/usr/bin/env bash

PROJECT=$(gcloud config get-value project)
echo "Project Number of project '$PROJECT' :"
PROJECT_NUMBER=$(gcloud projects list --filter="$PROJECT" --format="value(PROJECT_NUMBER)")
