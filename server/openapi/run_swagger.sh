#!/bin/bash
echo "regenerate openapi specs"
php ../generate_openapi_specs.php
echo "updating swagger"
npm update
echo "launching swagger UI"
cp rcq-openapi.yaml node_modules/swagger-ui-dist/

#Update the URL to our Yaml in case the swagger-ui-dist has been updated
sed -i '' 's,https://petstore.swagger.io/v2/swagger.json,rcq-openapi.yaml,g' node_modules/swagger-ui-dist/index.html


#Check if HTTPSTER is already running or not
HTTPSTER_RUNNING=$(netstat -an | grep 3333 | wc -l)

if [[ "${HTTPSTER_RUNNING}1" = "01" ]]
then
  httpster -d node_modules/swagger-ui-dist/ &
fi



/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome http://localhost:3333
