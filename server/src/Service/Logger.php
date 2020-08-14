<?php
namespace RedCrossQuest\Service;

use Psr\Log\LoggerInterface;
use RedCrossQuest\Entity\LoggingEntity;

class Logger implements LoggerInterface
{
  /** @var LoggerInterface $psrLogger*/
  private $psrLogger;
  /** @var SlackService $slackService */
  private $slackService;
  /** @var string $rcqInfo*/
  private $rcqInfo;
  /** @var bool $online*/
  private $online;
  /** @var string $localLogFile*/
  private $localLogFile;

  public function __construct(LoggerInterface $psrLogger, string $rcqVersion, string $rcqEnv, bool $online)
  {
    $this->psrLogger    = $psrLogger;
    $this->online       = $online;
    $this->rcqInfo      =
      [
        "rcqVersion" => $rcqVersion,
        "rcqEnv"     => $rcqEnv,
        "uri"        =>$_SERVER["REQUEST_URI"],
        "httpVerb"   =>$_SERVER["REQUEST_METHOD"]
      ];

    if(!$this->online)
    {
      $documentRoot = $_SERVER['DOCUMENT_ROOT'];
      $home = substr($documentRoot, 0, strpos($documentRoot, '/', 9));
      $this->localLogFile = "$home/RedCrossQuest/server/logs/local-logs.log";
    }

    $GLOBALS['LoggerService']=&$this;
  }
  //

  /**
   * to break circular Dependencies.
   * This method is manually called in the index.php after the DI container is built
   * The other solution with PHP-DI 6 is to use lazy loading, which requires other dependencies
   * @param SlackService $slackService
   */
  public function setSlackService(SlackService $slackService)
  {
    $this->slackService = $slackService;
  }

  /**
   *
   * store in the Request data that identify UL, User  or other data if those are not available (login process) + env & app version
   *
   * @param LoggingEntity $loggingEntity an instance of LogEntity
   *
   */
  public static function dataForLogging(LoggingEntity $loggingEntity):void
  {
    $_REQUEST['ESSENTIAL_LOGGING_INFO'] = $loggingEntity;
  }

  /**
   * Return a merge array of the array stored in $_REQUEST and the array passed in parameter + a set of basic info(see __construct)
   * @param array $dataToLog
   * @return array
   */
  private function getDataForLogging(array $dataToLog = array()):array
  {
    if(isset($_REQUEST['ESSENTIAL_LOGGING_INFO']))
      $dataForLogging = $_REQUEST['ESSENTIAL_LOGGING_INFO']->loggingInfoArray();
    else
      $dataForLogging = ['ESSENTIAL_LOGGING_INFO_NOT_SET'=>true];

    return ["appInfo"=>$this->rcqInfo, "logContext"=>$dataForLogging,"dataToBeLogged"=> $dataToLog];
  }


  /**
   * Log an emergency entry.
   *
   * Example:
   * ```
   * $psrLogger->emergency('emergency message');
   * ```
   *
   * @param string $message The message to log.
   * @param array $context [optional] Please see {@see \Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function emergency($message, array $context = array()):void
  {
    if($this->online)
    {
      $data = $this->getDataForLogging($context);

      $this->psrLogger   ->emergency  ($message, $data);
      $this->slackService->postMessage($message, $data);
    }
    else
    {
      error_log( PHP_EOL.date('Y-m-d\TH:i:s')."[EMERGENCY] ".$message." - ".json_encode($this->getDataForLogging($context)), 3, $this->localLogFile);
    }
  }

  /**
   * Log an alert entry.
   *
   * Example:
   * ```
   * $psrLogger->alert('alert message');
   * ```
   *
   * @param string $message The message to log.
   * @param array $context [optional] Please see {@see \Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function alert($message, array $context = array()):void
  {
    if($this->online)
    {
      $data = $this->getDataForLogging($context);

      $this->psrLogger   ->alert      ($message, $data);
      $this->slackService->postMessage($message, $data);
    }
    else
    {
      error_log( PHP_EOL.date('Y-m-d\TH:i:s')."[ALERT] ".$message." - ".json_encode($this->getDataForLogging($context)), 3, $this->localLogFile);
    }
  }

  /**
   * Log a critical entry.
   *
   * Example:
   * ```
   * $psrLogger->critical('critical message');
   * ```
   *
   * @param string $message The message to log.
   * @param array $context [optional] Please see {@see \Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function critical($message, array $context = array()):void
  {
    if($this->online)
    {
      $data = $this->getDataForLogging($context);

      $this->psrLogger   ->critical   ($message, $data);
      $this->slackService->postMessage($message, $data);
    }
    else
    {
      error_log( PHP_EOL.date('Y-m-d\TH:i:s')."[CRITICAL] ".$message." - ".json_encode($this->getDataForLogging($context)), 3, $this->localLogFile);
    }
  }

  /**
   * Log an error entry.
   *
   * Example:
   * ```
   * $psrLogger->error('error message');
   * ```
   *
   * @param string $message The message to log.
   * @param array $context [optional] Please see {@see \Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function error($message, array $context = array()):void
  {
    if($this->online)
    {
      $data = $this->getDataForLogging($context);

      $this->psrLogger   ->error      ($message, $data);
      $this->slackService->postMessage($message, $data);
    }
    else
    {
      error_log( PHP_EOL.date('Y-m-d\TH:i:s')."[ERROR] ".$message." - ".json_encode($this->getDataForLogging($context)), 3, $this->localLogFile);
    }
  }
  /**
   * Log a warning entry.
   *
   * Example:
   * ```
   * $psrLogger->warning('warning message');
   * ```
   *
   * @param string $message The message to log.
   * @param array $context [optional] Please see {@see \Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function warning($message, array $context = array()):void
  {
    if($this->online)
    {
      $this->psrLogger->warning($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log( PHP_EOL.date('Y-m-d\TH:i:s')."[WARN] ".$message." - ".json_encode($this->getDataForLogging($context)), 3, $this->localLogFile);
    }
  }
  /**
   * Log a notice entry.
   *
   * Example:
   * ```
   * $psrLogger->notice('notice message');
   * ```
   *
   * @param string $message The message to log.
   * @param array $context [optional] Please see {@see \Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function notice($message, array $context = array()):void
  {
    if($this->online)
    {
      $this->psrLogger->notice($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log( PHP_EOL.date('Y-m-d\TH:i:s')."[NOTICE] ".$message." - ".json_encode($this->getDataForLogging($context)), 3, $this->localLogFile);
    }
  }
  /**
   * Log an info entry.
   *
   * Example:
   * ```
   * $psrLogger->info('info message');
   * ```
   *
   * @param string $message The message to log.
   * @param array $context [optional] Please see {@see \Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function info($message, array $context = array()):void
  {
    if($this->online)
    {
      $this->psrLogger->info($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log( PHP_EOL.date('Y-m-d\TH:i:s')."[INFO] ".$message." - ".json_encode($this->getDataForLogging($context)), 3, $this->localLogFile);
    }
  }
  /**
   * Log a debug entry.
   *
   * Example:
   * ```
   * $psrLogger->debug('debug message');
   * ```
   *
   * @param string $message The message to log.
   * @param array $context [optional] Please see {@see \Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function debug($message, array $context = array()):void
  {
    if($this->online)
    {
      $this->psrLogger->debug($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log( PHP_EOL.date('Y-m-d\TH:i:s')."[DEBUG] ".$message." - ".json_encode($this->getDataForLogging($context)), 3, $this->localLogFile);
    }
  }

  /**
   * {@see \Google\Cloud\Logging\PsrLogger::log()}
   *
   * @param string|int $level The severity of the log entry.
   * @param string $message The message to log.
   * @param array $context   {@see \Google\Cloud\Logging\PsrLogger::log()}
   */
  public function log($level, $message, array $context = array()):void
  {
    if($this->online)
    {
      $this->psrLogger->log($level, $message, $this->getDataForLogging($context));
    }
    else
    {
      $array="";
      if(array_count_values($context)>0)
      {
        $array=json_encode($context);
      }
      error_log( PHP_EOL.date("Y-m-d H:i:s")." [".strtoupper($level)."] ".$message." - ".$array, 3, $this->localLogFile);
    }
  }
}
