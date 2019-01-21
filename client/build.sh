#! /bin/sh -ex
rm -rf ./dist/*
npm install
npm audit fix
bower install
gulp build

echo "done"
