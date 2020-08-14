<?php
namespace RedCrossQuest\Service;

use Exception;
use JoliCode\Slack\ClientFactory;
use JoliCode\Slack\Api\Client;

class SlackService
{
  /** @var Client $slackClient*/
  private $slackClient;

  /** @var Logger */
  protected $logger;

  /** @var string $rcqEnv*/
  private $rcqEnv;
  /** @var bool $online*/
  private $online;
  /** @var string $rcqVersion*/
  private $rcqVersion;

  private $envs=[
    "d" => "dev" ,
    "t" => "test",
    "p" => "prod"
  ];

  public function __construct(string $slackToken, string $rcqVersion, string $rcqEnv, bool $online)
  {
    $this->online     = $online;
    $this->rcqEnv     = $rcqEnv;
    $this->rcqVersion = $rcqVersion;

    if($this->online)
    {
      $this->slackClient = ClientFactory::create($slackToken);
    }
  }

  /**
   * to break circular Dependencies.
   * This method is manually called in the index.php after the DI container is built
   * The other solution with PHP-DI 6 is to use lazy loading, which requires other dependencies
   * @param Logger $logger
   */
  public function setLogger(Logger $logger)
  {
    $this->logger = $logger;
  }
  
  /**
   * {@see https://github.com/jolicode/slack-php-api/blob/master/doc/examples/posting-message.php}
   *
   * @param string $message The message to log.
   * @param array $context   {@see \Google\Cloud\Logging\PsrLogger::log()}
   */
  public function postMessage($message, array $context = array()):void
  {
    if($this->online)
    {
      try
      {
        $this->slackClient->chatPostMessage([
          'username'  => 'rcqlogger',
          'channel'   => 'monitoring-'.$this->envs[strtolower($this->rcqEnv)],
          'text'      => "*".$message."*\n```".json_encode($context, JSON_PRETTY_PRINT)."```",
        ]);
      }
      catch(Exception $e)
      {
        //DO NOT SWITCH TO ERROR LEVEL
        //otherwise it's likely to create a stackoverflow
        //as the logger at error level and above post a message on slack.
        //And since we're here, it's because the first call to slack was in error, and it's likely not solved in nanoseconds
        //DO NOT SWITCH TO ERROR LEVEL
        $this->logger->warning("can't post message on slack",
        //DO NOT SWITCH TO ERROR LEVEL
        [
          "message"=>$message,
          Logger::$EXCEPTION=>$e
        ]);
      }

    }
  }
}
