#! /bin/sh -ex

npm install
bower install
./node_modules/.bin/gulp build

echo "done"

