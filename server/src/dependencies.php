<?php

use \RedCrossQuest\BusinessService\EmailBusinessService;
use \RedCrossQuest\DBService\MailingDBService;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;

use \RedCrossQuest\Service\PubSubService;
use \RedCrossQuest\Service\ReCaptchaService;
use \RedCrossQuest\Service\MailService;
use RedCrossQuest\Service\ClientInputValidator;

use \RedCrossQuest\DBService\UserDBService;
use \RedCrossQuest\DBService\QueteurDBService;
use \RedCrossQuest\DBService\UniteLocaleDBService;
use \RedCrossQuest\DBService\SpotfireAccessDBService;
use \RedCrossQuest\DBService\TroncDBService;
use \RedCrossQuest\DBService\TroncQueteurDBService;
use \RedCrossQuest\DBService\PointQueteDBService  ;
use \RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use \RedCrossQuest\DBService\NamedDonationDBService;
use RedCrossQuest\DBService\UniteLocaleSettingsDBService;

use \RedCrossQuest\BusinessService\TroncQueteurBusinessService;
use \RedCrossQuest\BusinessService\SettingsBusinessService;
use \RedCrossQuest\BusinessService\ExportDataBusinessService;
use \RedCrossQuest\DBService\YearlyGoalDBService;

use Google\Cloud\Logging\LoggingClient;

use Google\Cloud\Logging\PsrLogger;

// DIC configuration
$container = $app->getContainer();

// view renderer
/**
 * @property \Slim\Views\PhpRenderer $renderer
 * @param    \Slim\Container $c
 * @return   \Slim\Views\PhpRenderer
 */
$container['renderer'] = function (\Slim\Container $c)
{
  $settings = $c->get('settings')['renderer'];
  return new \Slim\Views\PhpRenderer($settings['template_path']);
};


/**
 * @property PsrLogger    $logger
 * @param \Slim\Container $c
 * @return PsrLogger
 */
$container['logger'] = function (\Slim\Container $c)
{
  $settings = $c->get('settings')['logger'];

  $logger = LoggingClient::psrBatchLogger(
    $settings['name'], [
    'resource'=>[
      'type'=>'gae_app'
    ],
    'labels'  =>null
    ]);


  return $logger;
};

// DB connection
/**
 * @property PDO $db
 * @param \Slim\Container $c
 * @return PDO
 */
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
    $logger->error("Error while connecting to DB with parameters", array("dsn"=>$db['dsn'],'user'=>$db['user'],'pwd'=>strlen($db['pwd']), 'exception'=>$e));
    throw $e;
  }
};


//PubSub
/**
 * @property PubSubService $PubSub
 * @param \Slim\Container $c
 * @return PubSubService
 */
$container['PubSub'] = function (\Slim\Container $c)
{
  $settings = $c['settings']['PubSub'];
  return new PubSubService($settings, $c->logger);
};

//Google ReCaptcha v3
/**
 * @property ReCaptchaService $reCaptcha
 * @param \Slim\Container $c
 * @return ReCaptchaService
 */
$container['reCaptcha'] = function (\Slim\Container $c)
{
  return new ReCaptchaService($c['settings'], $c->logger);
};


/**
 * @property Bucket $bucket
 *
 * @param \Slim\Container $c
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

    $logger->error("An Error Occured",
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
/**
 * @property UserDBService $UserDBService
 * @param \Slim\Container $c
 * @return UserDBService
 */
$container['userDBService'] = function (\Slim\Container $c)
{
  return new UserDBService($c->db, $c->logger);
};
/**
 * @property SpotfireAccessDBService $SpotfireAccessDBService
 * @param \Slim\Container $c
 * @return SpotfireAccessDBService
 */
$container['spotfireAccessDBService'] = function (\Slim\Container $c)
{
  return new SpotfireAccessDBService($c->db, $c->logger);
};
/**
 * @property QueteurDBService $queteurDBService
 * @param \Slim\Container $c
 * @return QueteurDBService
 */
$container['queteurDBService'] = function (\Slim\Container $c)
{
  return new QueteurDBService($c->db, $c->logger);
};
/**
 * @property UniteLocaleDBService $uniteLocaleDBService
 * @param \Slim\Container $c
 * @return UniteLocaleDBService
 */
$container['uniteLocaleDBService'] = function (\Slim\Container $c)
{
  return new UniteLocaleDBService($c->db, $c->logger);
};


/**
 * @property UniteLocaleSettingsDBService $uniteLocaleSettingsDBService
 * @param \Slim\Container $c
 * @return UniteLocaleSettingsDBService
 */
$container['uniteLocaleSettingsDBService'] = function (\Slim\Container $c)
{
  return new UniteLocaleSettingsDBService($c->db, $c->logger);
};




/**
 * @property TroncDBService $troncDBService
 * @param \Slim\Container $c
 * @return TroncDBService
 */
$container['troncDBService'] = function (\Slim\Container $c)
{
  return new TroncDBService($c->db, $c->logger);
};
/**
 * @property TroncQueteurDBService $troncQueteurDBService
 * @param \Slim\Container $c
 * @return TroncQueteurDBService
 */
$container['troncQueteurDBService'] = function (\Slim\Container $c)
{
  return new TroncQueteurDBService($c->db, $c->logger);
};
/**
 * @property PointQueteDBService $pointQueteDBService
 * @param \Slim\Container $c
 * @return PointQueteDBService
 */
$container['pointQueteDBService'] = function (\Slim\Container $c)
{
  return new PointQueteDBService($c->db, $c->logger);
};
/**
 * @property DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService
 * @param \Slim\Container $c
 * @return DailyStatsBeforeRCQDBService
 */
$container['dailyStatsBeforeRCQDBService'] = function (\Slim\Container $c)
{
  return new DailyStatsBeforeRCQDBService($c->db, $c->logger);
};
/**
 * @property NamedDonationDBService $namedDonationDBService
 * @param \Slim\Container $c
 * @return NamedDonationDBService
 */
$container['namedDonationDBService'] = function (\Slim\Container $c)
{
  return new NamedDonationDBService($c->db, $c->logger);
};

/**
 * @property YearlyGoalDBService $yearlyGoalDBService
 * @param \Slim\Container $c
 * @return YearlyGoalDBService
 */
$container['yearlyGoalDBService'] = function (\Slim\Container $c)
{
  return new YearlyGoalDBService($c->db, $c->logger);
};

/**
 * @property MailingDBService $mailingDBService
 * @param \Slim\Container $c
 * @return MailingDBService
 */
$container['mailingDBService'] = function (\Slim\Container $c)
{
  return new MailingDBService($c->db, $c->logger);
};


/**
 * @property MailService $mailService
 * @param \Slim\Container $c
 * @return MailService
 */
$container['mailService'] = function (\Slim\Container $c)
{
  $settings =  $c['settings']['appSettings']['email'];

  return new MailService($c->logger,  $settings['sendgrid.api_key'], $settings['sendgrid.sender'], $c['settings']['appSettings']['deploymentType']);
};


/**
 * @property EmailBusinessService $mailer
 * @param \Slim\Container $c
 * @return EmailBusinessService
 */
$container['mailer'] = function (\Slim\Container $c)
{
  return new EmailBusinessService(
    $c->logger,
    $c->mailService,
    $c->mailingDBService,
    $c['settings']['appSettings']);
};

/* **********
 * SERVICES *
 * **********
 */

/**
 * @property TroncQueteurBusinessService $troncQueteurBusinessService
 * @param \Slim\Container $c
 * @return TroncQueteurBusinessService
 */
$container['troncQueteurBusinessService'] = function (\Slim\Container $c)
{
  return new TroncQueteurBusinessService( $c);
};
/**
 * @property SettingsBusinessService $settingsBusinessService
 * @param \Slim\Container $c
 * @return SettingsBusinessService
 */
$container['settingsBusinessService'] = function (\Slim\Container $c)
{
  return new SettingsBusinessService($c);
};
/**
 * @property ExportDataBusinessService $exportDataBusinessService
 * @param \Slim\Container $c
 * @return ExportDataBusinessService
 */
$container['exportDataBusinessService'] = function (\Slim\Container $c)
{
  return new ExportDataBusinessService($c);
};

/**
 * @property ClientInputValidator $clientInputValidator
 * @param \Slim\Container $c
 * @return ClientInputValidator
 */
$container['clientInputValidator'] = function (\Slim\Container $c)
{
  return new ClientInputValidator($c->logger);
};