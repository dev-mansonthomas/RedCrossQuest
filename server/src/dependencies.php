<?php

use \RedCrossQuest\BusinessService\EmailBusinessService;
use \RedCrossQuest\DBService\MailingDBService;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;

use \RedCrossQuest\Service\PubSubService;

// DIC configuration
$container = $app->getContainer();

// view renderer
$container['renderer'] = function (\Slim\Container $c)
{
  $settings = $c->get('settings')['renderer'];
  return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function (\Slim\Container $c)
{
  $settings = $c->get('settings')['logger'];

  $logger = new Monolog\Logger($settings['name']);
  $logger->pushProcessor(new Monolog\Processor\UidProcessor());
  $logger->pushHandler  (new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::INFO));

  return $logger;
};
// DB connection
$container['db'] = function (\Slim\Container $c)
{
  $db = $c['settings']['db'];

  try
  {
    $pdo = new PDO( $db['dsn'], $db['user'], $db['pwd']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE           , PDO::ERRMODE_EXCEPTION );
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC       );
    return $pdo;

  }
  catch(\Exception $e)
  {
    $logger = $c->get('logger');
    $logger->addError("Error while connecting to DB with parameters", array("dsn"=>$db['dsn'],'user'=>$db['user'],'pwd'=>strlen($db['pwd']), 'exception'=>$e));
    throw $e;
  }
};

//email

$container['mailer'] = function (\Slim\Container $c)
{
  $settings = $c['settings']['appSettings']['email'];
  return new EmailBusinessService($c->logger, $settings['sendgrid.api_key'], $settings['sendgrid.sender'], $c['settings']['appSettings'], new MailingDBService($c->db, $c->logger));
};

//PubSub
$container['PubSub'] = function (\Slim\Container $c)
{
  $settings = $c['settings']['PubSub'];
  return new PubSubService($settings, $c->logger);
};



/**
 * @param \Slim\Container container
 * @return Bucket
 */
$container['bucket'] = function (\Slim\Container $c)
{
  $appSettings = $c['settings']['appSettings'];

  $country        = $appSettings['country'         ];
  $env            = strtolower($appSettings['deploymentType'  ]);
  $bucketTemplate = $appSettings['exportDataBucket'];

  $envLabel=[];
  $envLabel['D'] = "dev";
  $envLabel['T'] = "test";
  $envLabel['P'] = "prod";

  $gcpBucket  = str_replace("_country_", $country, str_replace("_env_", $env           , $bucketTemplate));
  $project_id = str_replace("_country_", $country, str_replace("_env_", $envLabel[$env], "redcrossquest-_country_-_env_"));

  //documentation : https://cloud.google.com/storage/docs/reference/libraries
  //https://github.com/googleapis/google-cloud-php
  $storage = new StorageClient([
    'projectId' => $project_id
  ]);

  return $storage->bucket($gcpBucket);

};

$c['errorHandler'] = function (\Slim\Container $c) {
  return function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, \Exception $exception) use ($c)
  {
    $logger = $c->get('logger');

    $logger->addError("An Error Occured",
      array(
        'URI'     => $request->getUri(),
        'headers' => $request->getHeaders(),
        'body'    => $request->getBody()->getContents(),
        'exception'=>$exception)
    );


    return $c['response']->withStatus(500)
      ->withHeader('Content-Type', 'text/html')
      ->write('Something went wrong! - '.$exception->getMessage());
  };
};