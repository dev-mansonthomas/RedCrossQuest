<?php

$REDQUEST="rq";
$REDCROSSQUEST="rcq";

$cloudFunctions=[];

#see https://cloud.google.com/iam/docs/understanding-roles for list of roles
#grant roles in RCQ projects to RQ & RCQ Cloud functions

#In RQ-fr-xxx projects, we grant the cloud functions to access the local project secret manager and firestore

#In RCQ-fr-xxx we grant the rq-fr-xxx CF to access MySQL (hosted in RCQ)
#and we grant RCQ-fr-xxx cloud function to access mysql, secret manager and pubsub




$cloudFunctions["notifyRQOfRegistApproval"]=array(
  "trigger"       => "pubsub",
  "trigger_queue" => "queteur_approval_topic",
  "project"       =>  $REDCROSSQUEST,
  "roles"         => array(
          "RQ"  => ['roles/datastore.user'],
          "RCQ" => ['roles/pubsub.subscriber', 'roles/logging.logWriter']
  )
);

$cloudFunctions["ComputeULStats"]=array(
  "trigger"       => "http",
  "project"       =>  $REDCROSSQUEST,
  "roles"         => array(
    "RQ"  => ['roles/datastore.user'],
    "RCQ" => ['roles/cloudsql.client', 'roles/secretmanager.secretAccessor', 'roles/logging.logWriter']
  )
);

$cloudFunctions["ULTriggerRecompute"]=array(
  "trigger"       => "pubsub",
  "trigger_queue" => "trigger_ul_update",
  "project"       =>  $REDCROSSQUEST,
  "roles"         => array(
    "RQ"  => [],
    "RCQ" => ['roles/cloudsql.client', 'roles/secretmanager.secretAccessor', 'roles/pubsub.subscriber', 'roles/logging.logWriter', 'roles/cloudtasks.enqueuer']
  )
);

$cloudFunctions["findQueteurById"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/datastore.viewer','roles/secretmanager.secretAccessor','roles/logging.logWriter'],
    "RCQ" => ['roles/cloudsql.client']
  )
);

$cloudFunctions["findULDetailsByToken"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/secretmanager.secretAccessor','roles/logging.logWriter'],
    "RCQ" => ['roles/cloudsql.client']
  )
);

$cloudFunctions["getULPrefs"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/datastore.viewer','roles/logging.logWriter'],
    "RCQ" => ['roles/datastore.viewer']
  )
);

$cloudFunctions["getULStats"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/datastore.viewer','roles/logging.logWriter'],
    "RCQ" => ['roles/datastore.viewer']
  )
);

$cloudFunctions["historiqueTroncQueteur"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/datastore.user', 'roles/secretmanager.secretAccessor','roles/logging.logWriter'],
    "RCQ" => ['roles/datastore.user', 'roles/cloudsql.client']
  )
);

$cloudFunctions["registerQueteur"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/secretmanager.secretAccessor','roles/logging.logWriter'],
    "RCQ" => ['roles/cloudsql.client']
  )
);

$cloudFunctions["resyncQueteurIdToFirestore"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/datastore.user','roles/secretmanager.secretAccessor','roles/logging.logWriter'],
    "RCQ" => ['roles/cloudsql.client']
  )
);

$cloudFunctions["troncListPrepared"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/datastore.viewer','roles/secretmanager.secretAccessor','roles/logging.logWriter'],
    "RCQ" => ['roles/cloudsql.client']
  )
);

$cloudFunctions["troncSetDepartOrRetour"]=array(
  "trigger"       => "http",
  "project"       =>  $REDQUEST,
  "roles"         => array(
    "RQ"  => ['roles/datastore.viewer','roles/secretmanager.secretAccessor','roles/logging.logWriter'],
    "RCQ" => ['roles/cloudsql.client']
  )
);

$GLOBALS['cloudFunctions']=$cloudFunctions;

function getCurrentProject()
{
  return str_replace(array("\r", "\n"), '', shell_exec("gcloud config get-value project"));
}

function setCurrentProject($projectId)
{
  $CURRENT_PROJECT=getCurrentProject();
  
  if($CURRENT_PROJECT != $projectId)
  {
    echo "GCP Project was '$CURRENT_PROJECT', updating it to '$projectId'";
    #set current project to target project"
    echo shell_exec("gcloud config set project $projectId");
  }
}


function createOneCloudFunctionServiceAccount($cloudFunctionName, $currentProjectId)
{
  $targetProjectName  = $GLOBALS['cloudFunctions'][$cloudFunctionName]['project'];
  $currentProjectName = explode("-",$currentProjectId)[0];

  if($currentProjectName==$targetProjectName)
  {
    $shellCommand = "gcloud iam service-accounts create \"cf-".strtolower($cloudFunctionName)."\" ".
      "--description=\"Service Account for the cloud function '$cloudFunctionName'\" ".
      "--display-name=\"Service Account for the cloud function '$cloudFunctionName'\"";

    echo "=============================";
    echo $shellCommand."\n";
    //echo shell_exec($shellCommand);
    echo "\n=============================\n";
  }
  else
  {
    echo "$cloudFunctionName is running in project $targetProjectName not in $currentProjectName, nothing done\n\n";
  }
}

function createCloudFunctionServiceAccounts($currentProjectId)
{

  $cloudFunctions=$GLOBALS['cloudFunctions'];
  $functionNames = array_keys($cloudFunctions);
  foreach ($functionNames as $functionName)
  {
    createOneCloudFunctionServiceAccount($functionName, $currentProjectId );
  }
}


function initCloudFunctionsGrantRoles($currentProjectId)
{
  $cloudFunctions=$GLOBALS['cloudFunctions'];
  $functionNames = array_keys($cloudFunctions);
  foreach ($functionNames as $functionName)
  {
    initOneCloudFunctionsGrantRoles($functionName, $currentProjectId );
  }
}


function initOneCloudFunctionsGrantRoles($cloudFunctionName, $currentProjectId)
{
  $oneFunction              = $GLOBALS['cloudFunctions'][$cloudFunctionName];
  $targetProjectName        = $oneFunction['project'];
  $currentProjectIdExploded = explode("-",$currentProjectId, 2);
  $currentProjectName       = $currentProjectIdExploded[0];
  $countryAndEnv            = $currentProjectIdExploded[1];

  $roles = $oneFunction['roles']['$currentProjectName'];
  foreach($roles as $role)
  {
    $shellCommand = "gcloud projects add-iam-policy-binding $currentProjectId ".
      "--member serviceAccount:cf-".strtolower($cloudFunctionName)."@$targetProjectName-$countryAndEnv.iam.gserviceaccount.com ".
      "--role $role";
    echo "=============================";
    echo $shellCommand."\n";
    //echo shell_exec($shellCommand);
    echo "\n=============================\n";
  }
}
