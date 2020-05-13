#!/usr/bin/env bash

rm -rf ./dist/*
npm install
npm audit fix
bower install

#just in case a previous deployment didn't end normally
echo '{"deployDate": 20200202020202, "deployNotes": "DEPLOY_NOTES"}' > src/deploy.json
printf -v DEPLOY_DATE '%(%Y%m%d%H%M%S)T' -1

echo "setting version to $DEPLOY_DATE"
sed -i '' -e "s/20200202020202/${DEPLOY_DATE}/g" src/deploy.json

echo "updating deploy.json"
./buildVersionNotes.php

gulp build

#restore originale deploy.json
echo '{"deployDate": 20200202020202, "deployNotes": "DEPLOY_NOTES"}' > src/deploy.json

echo "done"
