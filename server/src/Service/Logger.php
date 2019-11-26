<?php


namespace RedCrossQuest\Service;


use Psr\Log\LoggerInterface;
use RedCrossQuest\Entity\LoggingEntity;

class Logger implements LoggerInterface
{

  private $psrLogger;
  private $rcqInfo;

  public function __construct(LoggerInterface $psrLogger, string $rcqVersion, string $rcqEnv)
  {
    $this->psrLogger  = $psrLogger  ;
    $this->rcqInfo    =
      [
        "rcqVersion" => $rcqVersion,
        "rcqEnv"     => $rcqEnv,
        "uri"        =>$_SERVER["REQUEST_URI"],
        "httpVerb"   =>$_SERVER["REQUEST_METHOD"]
      ];
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
    $dataForLogging = [];

    if(isset($_REQUEST['ESSENTIAL_LOGGING_INFO']))
      $dataForLogging = $_REQUEST['ESSENTIAL_LOGGING_INFO']->loggingInfoArray();

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
    $this->psrLogger->emergency($message, $this->getDataForLogging($context));
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
    $this->psrLogger->alert($message, $this->getDataForLogging($context));
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
    $this->psrLogger->critical($message, $this->getDataForLogging($context));
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
    $this->psrLogger->error($message, $this->getDataForLogging($context));
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
    $this->psrLogger->warning($message, $this->getDataForLogging($context));
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
    $this->psrLogger->notice($message, $this->getDataForLogging($context));
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
    $this->psrLogger->info($message, $this->getDataForLogging($context));
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
    $this->psrLogger->debug($message, $this->getDataForLogging($context));
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
    $this->psrLogger->log($level, $message, $this->getDataForLogging($context));
  }
}
