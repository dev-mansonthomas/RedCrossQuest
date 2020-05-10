<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\BusinessService\ExportDataBusinessService;
use RedCrossQuest\BusinessService\SettingsBusinessService;
use RedCrossQuest\BusinessService\TroncQueteurBusinessService;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\DBService\MailingDBService;
use RedCrossQuest\DBService\NamedDonationDBService;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\SpotfireAccessDBService;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\DBService\ULPreferencesFirestoreDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UniteLocaleSettingsDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\DBService\YearlyGoalDBService;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\MailService;
use RedCrossQuest\Service\PubSubService;
use RedCrossQuest\Service\ReCaptchaService;
use RedCrossQuest\Service\SecretManagerService;


return function (ContainerBuilder $containerBuilder)
{
  $containerBuilder->addDefinitions([

    /**
     * Logger 'googleLogger'
     * pecl install grpc
     * pecl install protobuf
     *
     */

    "googleLogger" => function (ContainerInterface $c)
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
    },

    /**
     * Version of RedCrossQuest
     */
    "RCQVersion" => function (ContainerInterface $c)
    {
      //version stays here, so that I don't have to update all the settings files
      return "2020.0";
    },
    /**
     * Custom Logger that automatically add context data to each log entries.
     * logger
     */
    LoggerInterface::class => function (ContainerInterface $c)
    {
      return new Logger($c->get("googleLogger"), (string)$c->get("RCQVersion"), $c->get('settings')['appSettings']['deploymentType'], $c->get('settings')['online']);
    },

    SecretManagerService::class => function (ContainerInterface $c)
    {
      return new SecretManagerService($c->get('settings'), $c->get(LoggerInterface::class));
    },
    
    "googleMapsApiKey" => function (ContainerInterface $c)
    {
      return $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$GOOGLE_MAPS_API_KEY);
    },

    /**
     * 'db'
     * DB connection
     */
    PDO::class => function (ContainerInterface $c)
    {
      $db = $c->get('settings')['db'];

      $dbPwd = $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$MYSQL_PASSWORD);
      try
      {
        $pdo = new PDO( $db['dsn'], $db['user'], $dbPwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE           , PDO::ERRMODE_EXCEPTION );
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC       );
        return $pdo;

      }
      catch(Exception $e)
      {
        $logger = $c->get(LoggerInterface::class);
        $logger->critical("Error while connecting to DB with parameters", array("dsn"=>$db['dsn'],'user'=>$db['user'],'pwd'=>strlen($dbPwd), 'exception'=>$e));
        throw $e;
      }
    },
    FirestoreClient::class => function(ContainerInterface $c)
    {
      return new FirestoreClient();
    },
    /**
     * 'PubSub'
     * Google PubSub service
     */
    PubSubService::class => function (ContainerInterface $c)
    {
      $settings = $c->get('settings')['PubSub'];
      return new PubSubService($settings, $c->get(LoggerInterface::class));
    },

    /**
     * 'reCaptcha'
     * Google ReCaptcha v3
     */
    ReCaptchaService::class => function (ContainerInterface $c)
    {
      $recaptchaSecret = $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$RECAPTCHA_SECRET);
      return new ReCaptchaService($c->get('settings'), $c->get(LoggerInterface::class), $recaptchaSecret);
    },

    /**
     * 'bucket'
     * Google Storage Bucket
     */
    Bucket::class => function (ContainerInterface $c)
    {
      $appSettings = $c->get('settings')['appSettings'];
      //TODO country is missing from settings
      $country        = $appSettings['country'         ];
      $bucketTemplate = $appSettings['exportDataBucket'];
      $env            = strtolower($appSettings['deploymentType'  ]);

      $envLabel=[];
      $envLabel['D'] = "dev";
      $envLabel['T'] = "test";
      $envLabel['P'] = "prod";

      $gcpBucket  = str_replace("_country_", $country, str_replace("_env_", $env           , $bucketTemplate));
      $project_id = str_replace("_country_", $country, str_replace("_env_", $envLabel[$env], "redcrossquest-_country_-_env_"));
      //TODO: check if projectID is mandatory, it shouldn't be
      //documentation : https://cloud.google.com/storage/docs/reference/libraries
      //https://github.com/googleapis/google-cloud-php
      $storage = new StorageClient([
        'projectId' => $project_id
      ]);

      return $storage->bucket($gcpBucket);
    },


    /**
     *
     */
    ULPreferencesFirestoreDBService::class => function (ContainerInterface $c)
    {
      return new ULPreferencesFirestoreDBService($c->get(FirestoreClient::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'userDBService'
     */
    UserDBService::class => function (ContainerInterface $c)
    {
      return new UserDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'spotfireAccessDBService'
     */
    SpotfireAccessDBService::class => function (ContainerInterface $c)
    {
      return new SpotfireAccessDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'queteurDBService'
     */
    QueteurDBService::class => function (ContainerInterface $c)
    {
      return new QueteurDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'uniteLocaleDBService'
     */
    UniteLocaleDBService::class => function (ContainerInterface $c)
    {
      return new UniteLocaleDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'uniteLocaleSettingsDBService'
     */
    UniteLocaleSettingsDBService::class => function (ContainerInterface $c)
    {
      return new UniteLocaleSettingsDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'troncDBService'
     */
    TroncDBService::class => function (ContainerInterface $c)
    {
      return new TroncDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'troncQueteurDBService'
     */
    TroncQueteurDBService::class => function (ContainerInterface $c)
    {
      return new TroncQueteurDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'pointQueteDBService'
     */
    PointQueteDBService::class => function (ContainerInterface $c)
    {
      return new PointQueteDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'dailyStatsBeforeRCQDBService'
     */
    DailyStatsBeforeRCQDBService::class => function (ContainerInterface $c)
    {
      return new DailyStatsBeforeRCQDBService($c->get('settings')['queteDates'], $c->get(PDO::class), $c->get(LoggerInterface::class));
    },


    /**
     * 'namedDonationDBService'
     */
    NamedDonationDBService::class => function (ContainerInterface $c)
    {
      return new NamedDonationDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'yearlyGoalDBService'
     */
    YearlyGoalDBService::class => function (ContainerInterface $c)
    {
      return new YearlyGoalDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'mailingDBService'
     */
    MailingDBService::class => function (ContainerInterface $c)
    {
      return new MailingDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'mailService'
     */
    MailService::class => function (ContainerInterface $c)
    {
      $settings       = $c->get('settings')['appSettings']['email'];
      $deploymentType = $c->get('settings')['appSettings']['deploymentType'];

      $sendgridApiKey = $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$SENDGRID_API_KEY);

      return new MailService($c->get(LoggerInterface::class),  $sendgridApiKey, $settings['sendgrid.sender'], $deploymentType);
    },

    /**
     * 'mailer'
     */
    EmailBusinessService::class => function (ContainerInterface $c)
    {
      return new EmailBusinessService(
        $c->get(LoggerInterface::class),
        $c->get(MailService::class),
        $c->get(MailingDBService::class),
        $c->get(UniteLocaleDBService::class),
        $c->get('settings')['appSettings']);
    },


    /* ********************
     *  BUSINESS SERVICES *
     * ********************
     */

    /**
     * 'troncQueteurBusinessService'
     */
    TroncQueteurBusinessService::class => function (ContainerInterface $c)
    {
      return new TroncQueteurBusinessService(
        $c->get(LoggerInterface::class),
        $c->get(TroncQueteurDBService::class),
        $c->get(QueteurDBService::class),
        $c->get(PointQueteDBService::class),
        $c->get(TroncDBService::class),
        $c->get(DailyStatsBeforeRCQDBService::class)
      );
    },

    /**
     * 'settingsBusinessService'
     */
    SettingsBusinessService::class => function (ContainerInterface $c)
    {
      return new SettingsBusinessService(
        $c->get(LoggerInterface::class),
        $c->get(QueteurDBService::class) ,
        $c->get(UserDBService::class) ,
        $c->get(PointQueteDBService::class) ,
        $c->get(DailyStatsBeforeRCQDBService::class) ,
        $c->get(TroncDBService::class) );
    },

    /**
     * 'exportDataBusinessService'
     */
    ExportDataBusinessService::class => function (ContainerInterface $c)
    {
      return new ExportDataBusinessService(
$c->get(LoggerInterface::class),
$c->get(QueteurDBService::class),
$c->get(PointQueteDBService::class),
$c->get(UserDBService::class),
$c->get(DailyStatsBeforeRCQDBService::class),
$c->get(TroncDBService::class),
$c->get(NamedDonationDBService::class),
$c->get(TroncQueteurDBService::class),
$c->get(UniteLocaleDBService::class),
$c->get(UniteLocaleSettingsDBService::class),
$c->get(YearlyGoalDBService::class)
      );
    },

    /**
     * 'clientInputValidator'
     */
    ClientInputValidator::class => function (ContainerInterface $c)
    {
      return new ClientInputValidator($c->get(LoggerInterface::class));
    },

    /**
     * 'firebase'
     * used to validate a firebase JWT
     */
    Firebase\Auth::class => function (ContainerInterface $c)
    {
      return (new Factory)->createAuth();
    },

  ]);// END addDefinitions()


};//END RETURN FUNCTION



