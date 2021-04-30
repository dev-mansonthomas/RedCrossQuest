<?php /** @noinspection PhpUnused */
declare(strict_types=1);

use DI\ContainerBuilder;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Logging\PsrLogger;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\BusinessService\ExportDataBusinessService;
use RedCrossQuest\BusinessService\SettingsBusinessService;
use RedCrossQuest\BusinessService\TroncQueteurBusinessService;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\DBService\MailingDBService;
use RedCrossQuest\DBService\MoneyBagDBService;
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
use RedCrossQuest\Service\RedCallService;
use RedCrossQuest\Service\SecretManagerService;
use RedCrossQuest\Service\SlackService;


return function (ContainerBuilder $containerBuilder)
{
  $containerBuilder->addDefinitions([

    /**
     * Logger 'googleLogger'
     * pecl install grpc
     * pecl install protobuf
     *
     */

    "googleLogger" => function (ContainerInterface $c):PsrLogger
    {
      $settings = $c->get('settings')['logger'];

      return LoggingClient::psrBatchLogger(
        $settings['name'], [
        'resource'=>[
          'type'=>'gae_app'
        ],
        'labels'  =>null
      ]);
    },

    /**
     * Version of RedCrossQuest
     */
    "RCQVersion" => function ():string
    {
      //version stays here, so that I don't have to update all the settings files
      return "2021.0";
    },
    /**
     * Custom Logger that automatically add context data to each log entries.
     * logger
     */
    LoggerInterface::class => function (ContainerInterface $c):Logger
    {
      return new Logger($c->get("googleLogger"), (string)$c->get("RCQVersion"), $c->get('settings')['appSettings']['deploymentType'], $c->get('settings')['online']);
    },
    Configuration::class => function (ContainerInterface $c):Configuration
    {
      $jwtSecret     = $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$JWT_SECRET);
      $signer        = new Sha256();
      $key           = InMemory::plainText($jwtSecret);
      $configuration = Configuration::forSymmetricSigner($signer, $key);

      $configuration->setValidationConstraints(
          new Lcobucci\JWT\Validation\Constraint\IssuedBy    ($c->get('settings')['jwt']['issuer'  ]),
          new Lcobucci\JWT\Validation\Constraint\PermittedFor($c->get('settings')['jwt']['audience']),
          new Lcobucci\JWT\Validation\Constraint\SignedWith  ($signer, $key)
      );

      return $configuration;
    },
    SecretManagerService::class => function (ContainerInterface $c):SecretManagerService
    {
      return new SecretManagerService($c->get('settings'), $c->get(LoggerInterface::class));
    },
    SlackService::class =>function(ContainerInterface $c):SlackService
    {
      $slackToken = $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$SLACK_TOKEN);
      return new SlackService($slackToken, (string)$c->get("RCQVersion"), $c->get('settings')['appSettings']['deploymentType'], $c->get('settings')['online']);
    },
    "googleMapsApiKey" => function (ContainerInterface $c):string
    {
      return $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$GOOGLE_MAPS_API_KEY);
    },

    /**
     * DB connection
     */
    PDO::class => function (ContainerInterface $c):PDO
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
        $logger->critical("Error while connecting to DB with parameters",
          array(
            'dsn'  =>$db['dsn'],
            'user' =>$db['user'],
            'pwd'  =>strlen($dbPwd),
            Logger::$EXCEPTION=>$e));
        throw $e;
      }
    },
    FirestoreClient::class => function():FirestoreClient
    {
      return new FirestoreClient();
    },

    /**
     * 'mailingDBService'
     */
    MailingDBService::class => function (ContainerInterface $c):MailingDBService
    {
      return new MailingDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'mailService'
     */
    MailService::class => function (ContainerInterface $c):MailService
    {
      $settings       = $c->get('settings')['appSettings']['email'];
      $deploymentType = $c->get('settings')['appSettings']['deploymentType'];

      $sendgridApiKey = $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$SENDGRID_API_KEY);

      return new MailService($c->get(LoggerInterface::class),  $sendgridApiKey, $settings['sendgrid.sender'], $deploymentType);
    },

    /**
     * Google PubSub service
     */
    PubSubService::class => function (ContainerInterface $c):PubSubService
    {
      $settings = $c->get('settings')['PubSub'];
      return new PubSubService($settings, $c->get(LoggerInterface::class));
    },

    
    /**
     * RedCall service
     */
    RedCallService::class => function (ContainerInterface $c):RedCallService
    {
      $settings      = $c->get('settings');
      $redCallSecret = $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$REDCALL_SECRET);

      return new RedCallService($settings, $redCallSecret, $c->get(LoggerInterface::class));
    },

    /**
     * Google ReCaptcha v3
     */
    ReCaptchaService::class => function (ContainerInterface $c):ReCaptchaService
    {
      $recaptchaSecret = $c->get(SecretManagerService::class)->getSecret(SecretManagerService::$RECAPTCHA_SECRET);
      return new ReCaptchaService($c->get('settings'), $c->get(LoggerInterface::class), $recaptchaSecret);
    },

    /**
     * Google Storage Bucket
     */
    Bucket::class => function (ContainerInterface $c):Bucket
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
      //documentation : https://cloud.google.com/storage/docs/reference/libraries
      //https://github.com/googleapis/google-cloud-php

      return (new StorageClient())->bucket($gcpBucket);
    },


    /**
     *
     */
    ULPreferencesFirestoreDBService::class => function (ContainerInterface $c):ULPreferencesFirestoreDBService
    {
      return new ULPreferencesFirestoreDBService($c->get(FirestoreClient::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'userDBService'
     */
    UserDBService::class => function (ContainerInterface $c):UserDBService
    {
      return new UserDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'spotfireAccessDBService'
     */
    SpotfireAccessDBService::class => function (ContainerInterface $c):SpotfireAccessDBService
    {
      return new SpotfireAccessDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'queteurDBService'
     */
    QueteurDBService::class => function (ContainerInterface $c):QueteurDBService
    {
      return new QueteurDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'uniteLocaleDBService'
     */
    UniteLocaleDBService::class => function (ContainerInterface $c):UniteLocaleDBService
    {
      return new UniteLocaleDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'uniteLocaleSettingsDBService'
     */
    UniteLocaleSettingsDBService::class => function (ContainerInterface $c):UniteLocaleSettingsDBService
    {
      return new UniteLocaleSettingsDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'troncDBService'
     */
    TroncDBService::class => function (ContainerInterface $c):TroncDBService
    {
      return new TroncDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'moneyBagDBService'
     */
    MoneyBagDBService::class => function (ContainerInterface $c): MoneyBagDBService
    {
      return new MoneyBagDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },
    /**
     * 'troncQueteurDBService'
     */
    TroncQueteurDBService::class => function (ContainerInterface $c):TroncQueteurDBService
    {
      return new TroncQueteurDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'pointQueteDBService'
     */
    PointQueteDBService::class => function (ContainerInterface $c):PointQueteDBService
    {
      return new PointQueteDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'dailyStatsBeforeRCQDBService'
     */
    DailyStatsBeforeRCQDBService::class => function (ContainerInterface $c):DailyStatsBeforeRCQDBService
    {
      return new DailyStatsBeforeRCQDBService($c->get('settings')['queteDates'], $c->get(PDO::class), $c->get(LoggerInterface::class));
    },


    /**
     * 'namedDonationDBService'
     */
    NamedDonationDBService::class => function (ContainerInterface $c):NamedDonationDBService
    {
      return new NamedDonationDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },

    /**
     * 'yearlyGoalDBService'
     */
    YearlyGoalDBService::class => function (ContainerInterface $c):YearlyGoalDBService
    {
      return new YearlyGoalDBService($c->get(PDO::class), $c->get(LoggerInterface::class));
    },


    /* ********************
     *  BUSINESS SERVICES *
     * ********************
     */

    /**
     * 'mailer'
     */
    EmailBusinessService::class => function (ContainerInterface $c):EmailBusinessService
    {
      return new EmailBusinessService(
        $c->get(LoggerInterface::class),
        $c->get(MailService::class),
        $c->get(MailingDBService::class),
        $c->get(UniteLocaleDBService::class),
        $c->get('settings')['appSettings']);
    },

    /**
     * 'troncQueteurBusinessService'
     */
    TroncQueteurBusinessService::class => function (ContainerInterface $c):TroncQueteurBusinessService
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
    SettingsBusinessService::class => function (ContainerInterface $c):SettingsBusinessService
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
    ExportDataBusinessService::class => function (ContainerInterface $c):ExportDataBusinessService
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
    ClientInputValidator::class => function (ContainerInterface $c):ClientInputValidator
    {
      return new ClientInputValidator($c->get(LoggerInterface::class));
    },

    /**
     * 'firebase'
     * used to validate a firebase JWT
     */
    Auth::class => function ():Auth
    {
      return (new Factory)->createAuth();
    },

  ]);// END addDefinitions()


};//END RETURN FUNCTION



