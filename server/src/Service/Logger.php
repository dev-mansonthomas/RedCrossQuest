<?php
namespace RedCrossQuest\Service;

use Exception;
use Psr\Log\LoggerInterface;
use RedCrossQuest\Entity\LoggingEntity;

class Logger implements LoggerInterface
{
  /** @var LoggerInterface $psrLogger*/
  private LoggerInterface $psrLogger;
  /** @var SlackService $slackService */
  private SlackService $slackService;
  /** @var array $rcqInfo*/
  private array $rcqInfo;
  /** @var bool $online*/
  private bool $online;
  /** @var string $localLogFile*/
  private string $localLogFile;
  /** @var bool $localLogWritable */
  private bool $localLogWritable;

  public static string $EXCEPTION="exception";

  public function __construct(LoggerInterface $psrLogger, string $rcqVersion, string $rcqEnv, bool $online)
  {
    $this->psrLogger    = $psrLogger;
    $this->online       = $online;
    $this->rcqInfo      =
      [
        "rcqVersion" => $rcqVersion,
        "rcqEnv"     => $rcqEnv,
        "uri"        =>$_SERVER["REQUEST_URI"]    ?? '',
        "httpVerb"   =>$_SERVER["REQUEST_METHOD"] ?? ''
      ];

    // Local log file lives at <repo>/server/logs/local-logs.log (mounted from
    // host under Docker via ./server:/app/server, so logs are tail-able from
    // the host editor / IDE).
    //
    // Write policy (cf. writeLocal()) :
    //   - $online === false (no GCP)   : seul destinataire des logs.
    //   - $online === true  + FS ecrivable (dev local connecte a GCP dev) :
    //       ecriture parallele EN PLUS de psrLogger (GCP Logging) + Slack,
    //       pour debug local sans aller-retour Stackdriver.
    //   - $online === true  + FS read-only (GAE prod/test/dev) :
    //       $localLogWritable=false en constructeur => no-op silencieux.
    $logsDir                = __DIR__ . '/../../logs';
    $this->localLogFile     = $logsDir . '/local-logs.log';
    $this->localLogWritable = is_dir($logsDir) && is_writable($logsDir);
  }
  /**
   * to break circular Dependencies.
   * This method is manually called in the index.php after the DI container is built
   * The other solution with PHP-DI 6 is to use lazy loading, which requires other dependencies
   * @param SlackService $slackService
   */
  public function setSlackService(SlackService $slackService): void
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

    if(array_key_exists(Logger::$EXCEPTION, $dataToLog) && $dataToLog[Logger::$EXCEPTION]!=null)
    {
      /** @var Exception $exception*/
      $exception = $dataToLog[Logger::$EXCEPTION];
      $dataToLog[Logger::$EXCEPTION] = [
        "message"   =>$exception->getMessage(),
        "stackTrace"=>PHP_EOL.$exception->getTraceAsString()
      ];
     // $dataToLog[Logger::$EXCEPTION] = substr(str_replace("\\","",str_replace("\\\\\\\\","/",json_encode($dataToLog[Logger::$EXCEPTION], JSON_PRETTY_PRINT))), 0, 2000);
    }

    return ["appInfo"=>$this->rcqInfo, "logContext"=>$dataForLogging,"dataToBeLogged"=> $dataToLog];
  }


  /**
   * Write a log entry to the local file. No-op when $localLogWritable is false
   * (typical case on GAE prod where /app is read-only). Errors are suppressed
   * via @-operator to avoid cascading from the logger itself.
   */
  private function writeLocal(string $level, string $message, array $data): void
  {
    if (!$this->localLogWritable)
    {
      return;
    }
    @error_log(
      PHP_EOL.date('Y-m-d\TH:i:s')."[$level] ".$message." - ".json_encode($data),
      3,
      $this->localLogFile
    );
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger   ->emergency  ($message, $data);
      $this->slackService->postMessage($message, $data);
    }
    $this->writeLocal('EMERGENCY', $message, $data);
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger   ->alert      ($message, $data);
      $this->slackService->postMessage($message, $data);
    }
    $this->writeLocal('ALERT', $message, $data);
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger   ->critical   ($message, $data);
      $this->slackService->postMessage($message, $data);
    }
    $this->writeLocal('CRITICAL', $message, $data);
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger   ->error      ($message, $data);
      $this->slackService->postMessage($message, $data);
    }
    $this->writeLocal('ERROR', $message, $data);
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger->warning($message, $data);
    }
    $this->writeLocal('WARN', $message, $data);
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger->notice($message, $data);
    }
    $this->writeLocal('NOTICE', $message, $data);
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger->info($message, $data);
    }
    $this->writeLocal('INFO', $message, $data);
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger->debug($message, $data);
    }
    $this->writeLocal('DEBUG', $message, $data);
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
    $data = $this->getDataForLogging($context);
    if($this->online)
    {
      $this->psrLogger->log($level, $message, $data);
    }
    $this->writeLocal(strtoupper((string)$level), $message, $data);
  }
}
