#!/usr/local/bin/php
<?php


$country= $argv[1];
$env    = $argv[2];
$target = $argv[3];


if(empty($country) || $country != "fr")
{
  echo "'$country' the first parameter (country) is not valid. Valid values are ['fr']\n";
  exit(1);
}

$envAcceptedValues=["dev","test","prod"];
if(empty($env) && !in_array($env, $envAcceptedValues))
{
  echo "'$env' the second parameter (env) is not valid. Valid values are ['dev', 'test', 'prod']\n";
  exit(1);
}

if(empty($target))
{
  $target = "ALL";
}


setProject("rcq", $country, $env);

$REPOSITORY_ID=[];
$REPOSITORY_ID["nodejs"]="github_dev-mansonthomas_redcrossquestcloudfunctions";
$REPOSITORY_ID["python"]="";
$RUNTIME="nodejs12";
$REGION="europe-west1";






function setProject($prefix, $country, $env)
{
  $currentProject = shell_exec("gcloud config get-value project");

  //print_r(unpack("C*", $currentProject));

  $currentProject = str_replace("\n","",$currentProject);


  echo "Current Project : '".$currentProject."'\n";
  $targetProject = "$prefix-$country-$env";

  if($targetProject != $currentProject)
  {
    echo "GCP Project was '$currentProject', updating it to '$targetProject'\n\n";
    #set current project to target project"
    $shellOutput = shell_exec("gcloud config set project '$targetProject'");

    $shellOutput = str_replace("\n","",$shellOutput);
    echo    $shellOutput."\n";
  }
}



