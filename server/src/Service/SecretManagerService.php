<?php
namespace RedCrossQuest\Service;

require '../../vendor/autoload.php';

use Google\ApiCore\ApiException;
use Google\Cloud\SecretManager\V1beta1\SecretManagerServiceClient;
use InvalidArgumentException;

 /**
  * retrieve secrets from Google Secret Manager
  * has a offline mode, where the secrets are retrieved from a local properties file
  */
class SecretManagerService
{
  protected $settings;
  /** @var Logger */
  protected $logger;
  /** @var SecretManagerServiceClient */
  protected $secretManagerServiceClient;

  public static $GOOGLE_RECAPTCHA_KEY = "GOOGLE_RECAPTCHA_KEY";
  public static $RECAPTCHA_SECRET     = "RECAPTCHA_SECRET";
  public static $GOOGLE_MAPS_API_KEY  = "GOOGLE_MAPS_API_KEY";
  public static $SENDGRID_API_KEY     = "SENDGRID_API_KEY";
  public static $MYSQL_PASSWORD       = "MYSQL_PASSWORD";
  public static $JWT_SECRET           = "JWT_SECRET";

  /** @var array */
  private $SECRET_NAME_ID_MAPPING;

  /** @var boolean if false, it's considered that it's on dev machine and offline or with poor internet connectivity */
  private $online=false;

  /** @var array */
  private $devSecret = null;
  private $devSecretFilePath = "/.cred/rcq-fr-dev-local.properties";

  /** @var string $secretNamePrefix on dev local dev environment, secret key names are prefixed by local- */
  private $secretNamePrefix="";

  private $envs=[
     "d" => "dev" ,
     "t" => "test",
     "p" => "prod"
    ];

  public function __construct($settings, Logger $logger)
  {
    $this->settings  = $settings;
    $this->logger    = $logger;
    $this->online    = $this->settings['online'];

    $env = strtolower($this->settings['appSettings']['deploymentType']);
    if($env == "d" && (strpos(strtolower($this->settings['appSettings']['appUrl']), 'localhost') > 0||
                       strpos(strtolower($this->settings['appSettings']['appUrl']), 'rcq'      ) > 0))
    {
      $this->secretNamePrefix="local-";
      $this->logger->warning("using local mode for secret names");
    }

    $PROJECT_ID = "rcq-fr-".$this->envs[$env];

//projects/$PROJECT_ID/secrets/&#42;/versions/*
    $this->SECRET_NAME_ID_MAPPING = [
      "GOOGLE_RECAPTCHA_KEY" => "projects/$PROJECT_ID/secrets/".$this->secretNamePrefix."GOOGLE_RECAPTCHA_KEY/versions/latest",
      "RECAPTCHA_SECRET"     => "projects/$PROJECT_ID/secrets/".$this->secretNamePrefix."RECAPTCHA_SECRET/versions/latest",
      "GOOGLE_MAPS_API_KEY"  => "projects/$PROJECT_ID/secrets/".$this->secretNamePrefix."GOOGLE_MAPS_API_KEY/versions/latest",
      "SENDGRID_API_KEY"     => "projects/$PROJECT_ID/secrets/".$this->secretNamePrefix."SENDGRID_API_KEY/versions/latest",
      "MYSQL_PASSWORD"       => "projects/$PROJECT_ID/secrets/".$this->secretNamePrefix."MYSQL_PASSWORD/versions/latest",
      "JWT_SECRET"           => "projects/$PROJECT_ID/secrets/".$this->secretNamePrefix."JWT_SECRET/versions/latest"
    ];

    $this->secretManagerServiceClient    = new SecretManagerServiceClient();
  }


  /**
   * @param string $secretName
   * @return string the secret value
   * @throws ApiException
   */
  public function getSecret(string $secretName)
  {
    //this will fault if the secretName is not know, so useful in the offline mode as well
    $secretId = $this->getSecretName($secretName);

    if($this->online)
    {
      try
      {
        $response = $this->secretManagerServiceClient->accessSecretVersion($secretId);
        return trim($response->getPayload()->getData());//\n at the end of the value
      }
      catch (ApiException $apiException)
      {
        $this->logger->critical("Error while retrieving secret",
          array("secret_name"=>$secretName, "secret_id"=>$secretId));
        throw $apiException;
      }
    }
    else
    {
      if($this->devSecret == null)
      {//load the file only once per run
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $home = substr($documentRoot, 0, strpos($documentRoot, '/', 9));
        $this->devSecret = $this->getPropertiesFromFile($home.$this->devSecretFilePath);
      }

      return $this->devSecret[$secretName];
    }

  }

  private function getSecretName(string $secretName)
  {
    if(!array_key_exists($secretName, $this->SECRET_NAME_ID_MAPPING))
    {
      throw new InvalidArgumentException("Invalid secret name. ".$secretName);
    }
    return $this->SECRET_NAME_ID_MAPPING[$secretName];
  }


  private function getPropertiesFromFile($txtPropertiesPath)
  {
    if(($txtProperties = file_get_contents($txtPropertiesPath)) === false)
    {
      throw new InvalidArgumentException("File not found or not readable. $txtPropertiesPath");
    }
    $result = array();
    $lines  = explode(PHP_EOL, $txtProperties);

    foreach($lines as $i=>$line)
    {
      if(empty($line) || strpos($line,"#") === 0)
        continue;

      $key   = substr($line,0,strpos($line,'='));
      if($key != "")
      {
        $value = substr($line,strpos($line,'=') + 1, strlen($line));
        $result[$key] = $value;
      }
    }
    return $result;
  }
}
