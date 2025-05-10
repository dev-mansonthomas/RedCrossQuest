<?php
namespace RedCrossQuest\Service;

use Google\ApiCore\ApiException;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\AccessSecretVersionRequest;
use InvalidArgumentException;

 /**
  * retrieve secrets from Google Secret Manager
  * has a offline mode, where the secrets are retrieved from a local properties file
  */
class SecretManagerService
{
  protected $settings;

  /** @var SecretManagerServiceClient */
  protected $secretManagerServiceClient;

  public static $GOOGLE_RECAPTCHA_KEY = "GOOGLE_RECAPTCHA_KEY";
  public static $RECAPTCHA_SECRET     = "RECAPTCHA_SECRET";
  public static $GOOGLE_MAPS_API_KEY  = "GOOGLE_MAPS_API_KEY";
  public static $SENDGRID_API_KEY     = "SENDGRID_API_KEY";
  public static $MYSQL_PASSWORD       = "MYSQL_PASSWORD";
  public static $JWT_SECRET           = "JWT_SECRET";
  public static $SLACK_TOKEN          = "SLACK_TOKEN";
  public static $REDCALL_SECRET       = "REDCALL_SECRET";

  /** @var array */
  private $SECRET_NAME_ID_MAPPING;
  /** @var string */
  private $PROJECT_ID;

  /** @var boolean if false, it's considered that it's on dev machine and offline or with poor internet connectivity */
  private $online;

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

  public function __construct($settings)
  {
    $this->settings  = $settings;
    $this->online    = $this->settings['online'];

    $env = strtolower($this->settings['appSettings']['deploymentType']);
    if($env == "d" && (strpos(strtolower($this->settings['appSettings']['appUrl']), 'localhost') > 0||
                       strpos(strtolower($this->settings['appSettings']['appUrl']), 'rcq'      ) > 0))
    {
      $this->secretNamePrefix="local-";
      //$this->logger->warning("using local mode for secret names");
    }

    $this->PROJECT_ID = "rcq-fr-".$this->envs[$env];

    $this->secretManagerServiceClient    = new SecretManagerServiceClient();
  }


  /**
   * @param string $secretName
   * @return string the secret value
   * @throws ApiException
   */
  public function getSecret(string $secretName):string
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
        error_log("Error while retrieving secret secret_name:'$secretName', secret_id:'$secretId'");

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

  /**
   * @param string $secretName Take a parameter name, as define in this class static variable
   * @return AccessSecretVersionRequest the formatted SecretManager name of the secret  (projects/$PROJECT_ID/secrets/".$this->secretNamePrefix."JWT_SECRET/versions/latest)
   */
  private function getSecretName(string $secretName):AccessSecretVersionRequest
  {
    //projects/$PROJECT_ID/secrets/&#42;/versions/latest
    $request = new AccessSecretVersionRequest();
    $name = "projects/$this->PROJECT_ID/secrets/".$this->secretNamePrefix.$secretName."/versions/latest";
    $request->setName($name);
    return $request;
  }

  /**
   * This function is used in offline mode(setting.php), when the SecretManager is not accessible or with a lousy internet connection
   * @param string $txtPropertiesPath file path of the properties file
   * @return array an associative array of the local secret SECRET_NAME=>SECRET_VALUE
   */
  private function getPropertiesFromFile(string $txtPropertiesPath):array
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
