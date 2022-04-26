#!/usr/local/bin/php
<?php

require("common.php");


$COUNTRY=$argv[1];
$ENV=$argv[2];
$CREATE_OR_UPDATE=$argv[3];
$FUNC_NAME=$argv[4];

if($COUNTRY != "fr")
{
  echo "'${COUNTRY}' the first parameter (country) is not valid. Valid values are ['fr']";
  exit(1);
}

if($ENV != "dev" && $ENV != "test" && $ENV != "prod" )
{
  echo "'${ENV}' the second parameter (env) is not valid. Valid values are ['dev', 'test', 'prod']";
  exit(2);
}

if($CREATE_OR_UPDATE != "create" && $CREATE_OR_UPDATE != "update")
{
  echo "'$CREATE_OR_UPDATE' the second parameter (CREATE_OR_UPDATE) is not valid. Valid values are ['create', 'update']. Create: create and set roles. Updates :  set roles only";
  exit(3);
}

// first create the services accounts if required so (and later add the roles to the services accounts)
if($CREATE_OR_UPDATE == "create")
{
  #create service accounts and then grant roles
  $PROJECT_NAME="rcq";
  $PROJECT_ID="$PROJECT_NAME-$COUNTRY-$ENV";
  echo "switching to ${PROJECT_ID}";

  setCurrentProject($PROJECT_ID);

  if($FUNC_NAME == "")
  {
    createCloudFunctionServiceAccounts($PROJECT_ID);
  }
  else
  {
    createOneCloudFunctionServiceAccount($FUNC_NAME, $PROJECT_ID);
  }

  $PROJECT_NAME="rq";
  $PROJECT_ID="$PROJECT_NAME-$COUNTRY-$ENV";
  setCurrentProject($PROJECT_ID);

  if($FUNC_NAME == "")
  {
    createCloudFunctionServiceAccounts($PROJECT_ID);
  }
  else
  {
    createOneCloudFunctionServiceAccount($FUNC_NAME, $PROJECT_ID);
  }
}
else
{//position the current project to RQ to optimize the number of change of current project
  $PROJECT_NAME="rq";
  $PROJECT_ID="$PROJECT_NAME-$COUNTRY-$ENV";
  setCurrentProject($PROJECT_ID);
}

// update the roles to the functions

if($FUNC_NAME == "")
{
  initCloudFunctionsGrantRoles($PROJECT_ID);
}
else
{
  initOneCloudFunctionsGrantRoles($FUNC_NAME, $PROJECT_ID);
}


$PROJECT_NAME="rcq";
$PROJECT_ID="$PROJECT_NAME-$COUNTRY-$ENV";
echo "switching to ${PROJECT_ID}";

if($FUNC_NAME == "")
{
  initCloudFunctionsGrantRoles($PROJECT_ID);
}
else
{
  initOneCloudFunctionsGrantRoles($FUNC_NAME, $PROJECT_ID);
}
