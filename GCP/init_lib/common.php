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




function createCloudFunctionServiceAccount($cloudFunctionName)
{
  $shellCommand = "gcloud iam service-accounts create \"cf-".strtolower($cloudFunctionName)."\" --description=\"Service Account for the cloud function '$cloudFunctionName'\" --display-name=\"Service Account for the cloud function '$cloudFunctionName'\"";
}
