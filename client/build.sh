#! /bin/sh -ex
rm -rf ./dist/*
npm install
bower install
gulp build

echo "done"
