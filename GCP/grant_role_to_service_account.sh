#!/bin/bash
set -euo pipefail

SA_EMAIL="thomas-dev@rcq-fr-dev.iam.gserviceaccount.com"
OWNER_ACCOUNT="mt@mansonthomas.com"
PROJECTS=("rcq-fr-dev" "rcq-fr-test" "rcq-fr-prod" "rq-fr-dev" "rq-fr-test" "rq-fr-prod")

echo "🔐 Switching to $OWNER_ACCOUNT"
gcloud config set account "$OWNER_ACCOUNT"

for PROJECT_ID in "${PROJECTS[@]}"; do
  echo "➡️ Processing project: $PROJECT_ID"

  if [[ $PROJECT_ID == rcq-* ]]; then
    echo "  🏗️  Granting App Engine / Cloud Functions / SQL / Storage / IAM roles..."

    ROLES=(
      roles/appengine.deployer
      roles/appengine.serviceAdmin
      roles/appengine.appAdmin
      roles/cloudfunctions.admin
      roles/cloudsql.client
      roles/storage.objectAdmin
      roles/cloudbuild.builds.editor
    )

    for ROLE in "${ROLES[@]}"; do
      echo "    ➕ Granting $ROLE on $PROJECT_ID"
      gcloud projects add-iam-policy-binding "$PROJECT_ID" \
        --member="serviceAccount:$SA_EMAIL" \
        --role="$ROLE" \
        --quiet
    done

    # Impersonation right on App Engine default SA
    APP_ENGINE_SA="$PROJECT_ID@appspot.gserviceaccount.com"
    echo "    🔄 Granting iam.serviceAccountUser on $APP_ENGINE_SA"
    gcloud iam service-accounts add-iam-policy-binding "$APP_ENGINE_SA" \
      --member="serviceAccount:$SA_EMAIL" \
      --role="roles/iam.serviceAccountUser" \
      --project="$PROJECT_ID" \
      --quiet

  else
    echo "  🌐 Granting Firebase roles..."

    ROLES=(
      roles/firebase.developAdmin
      roles/firebasehosting.admin
      roles/cloudfunctions.admin
      roles/viewer
    )

    for ROLE in "${ROLES[@]}"; do
      echo "    ➕ Granting $ROLE on $PROJECT_ID"
      gcloud projects add-iam-policy-binding "$PROJECT_ID" \
        --member="serviceAccount:$SA_EMAIL" \
        --role="$ROLE" \
        --quiet
    done
  fi
done

echo "🔄 Switching back to $SA_EMAIL"
gcloud config set account "$SA_EMAIL"

echo "✅ All permissions successfully applied."
