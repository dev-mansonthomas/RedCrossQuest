<?php


namespace RedCrossQuest\Service;


use Psr\Log\LoggerInterface;
use RedCrossQuest\Entity\LoggingEntity;

class Logger implements LoggerInterface
{
  /** @var LoggerInterface $psrLogger*/
  private $psrLogger;
  /** @var string $rcqInfo*/
  private $rcqInfo;
  /** @var bool $online*/
  private $online;
  /** @var string $localLogFile*/
  private $localLogFile;

  public function __construct(LoggerInterface $psrLogger, string $rcqVersion, string $rcqEnv, bool $online)
  {
    $this->online     = $online;
    $this->psrLogger  = $psrLogger;
    $this->rcqInfo    =
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

  }

  /**
   *
   * store in the Request data that identify UL, User  or other data if thoses are not available (login process) + env & app version
   *
   * @param LoggingEntity $loggingEntity an instance of LogEntity
   *
   */
  public static function dataForLogging(LoggingEntity $loggingEntity)
  {
    $_REQUEST['ESSENTIAL_LOGGING_INFO'] = $loggingEntity;
  }

  private function getDataForLogging(array $dataToLog = array())
  {
    if(isset($_REQUEST['ESSENTIAL_LOGGING_INFO']))
      $dataForLogging = $_REQUEST['ESSENTIAL_LOGGING_INFO']->loggingInfoArray();
    else
      $dataForLogging = ['ESSENTIAL_LOGGING_INFO_NOT_SET'=>true];

    return array_merge($this->rcqInfo, $dataForLogging, $dataToLog);
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
   * @param array $context [optional] Please see {@see Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function emergency($message, array $context = array())
  {
    if($this->online)
    {
      $this->psrLogger->emergency($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log("[EMERGENCY] ".$message." - ".print_r($context, true), 3, $this->localLogFile);
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
   * @param array $context [optional] Please see {@see Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function alert($message, array $context = array())
  {
    if($this->online)
    {
      $this->psrLogger->alert($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log("[ALERT] ".$message." - ".print_r($context, true), 3, $this->localLogFile);
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
   * @param array $context [optional] Please see {@see Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function critical($message, array $context = array())
  {
    if($this->online)
    {
      $this->psrLogger->critical($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log("[CRITICAL] ".$message." - ".print_r($context, true), 3, $this->localLogFile);
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
   * @param array $context [optional] Please see {@see Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function error($message, array $context = array())
  {
    if($this->online)
    {
      $this->psrLogger->error($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log("[ERROR] ".$message." - ".print_r($context, true), 3, $this->localLogFile);
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
   * @param array $context [optional] Please see {@see Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function warning($message, array $context = array())
  {
    if($this->online)
    {
      $this->psrLogger->warning($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log("[WARN] ".$message." - ".print_r($context, true), 3, $this->localLogFile);
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
   * @param array $context [optional] Please see {@see Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function notice($message, array $context = array())
  {
    if($this->online)
    {
      $this->psrLogger->notice($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log("[NOTICE] ".$message." - ".print_r($context, true), 3, $this->localLogFile);
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
   * @param array $context [optional] Please see {@see Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function info($message, array $context = array())
  {
    if($this->online)
    {
      $this->psrLogger->info($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log("[INFO] ".$message." - ".print_r($context, true), 3, $this->localLogFile);
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
   * @param array $context [optional] Please see {@see Google\Cloud\Logging\PsrLogger::log()}
   *        for the available options.
   */
  public function debug($message, array $context = array())
  {
    if($this->online)
    {
      $this->psrLogger->debug($message, $this->getDataForLogging($context));
    }
    else
    {
      error_log("[DEBUG] ".$message." - ".print_r($context, true), 3, $this->localLogFile);
    }
  }

  /**
    {@see Google\Cloud\Logging\PsrLogger::log()}
   *
   * @param string|int $level The severity of the log entry.
   * @param string $message The message to log.
   * @param array $context   {@see Google\Cloud\Logging\PsrLogger::log()}
   */
  public function log($level, $message, array $context = array())
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
        $array=print_r($context, true);
      }
      error_log(date("Y-m-d H:i:s")." [".strtoupper($level)."] ".$message." - ".$array, 3, $this->localLogFile);
    }

  }
}
