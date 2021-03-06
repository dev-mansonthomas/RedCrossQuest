<?php
namespace RedCrossQuest\Service;

use Exception;
use Google\Cloud\PubSub\PubSubClient;


/**
 * Created by IntelliJ IDEA.
 * User: thomas
 * Date: 08/11/2018
 * Time: 15:56
 */

class PubSubService
{
  protected $settings;
  /** @var Logger */
  protected $logger;
  /** @var PubSubClient */
  protected $pubSub;

  public function __construct($settings, Logger $logger)
  {
    $this->settings  = $settings;
    $this->logger    = $logger;
    $this->pubSub    = new PubSubClient();
  }

  /**
   * Publish data on Google Cloud Pub/Sub
   * @param string $topicName : the topic name
   * @param $data : the data to be sent
   * @param array $attributes : attributes of the message published on the topic (associated array : 'location' => 'Detroit')
   * @param bool $jsonEncodeData : should the $data be passed to json_encode before sending it
   * @param bool $raiseExceptionInCaseOfError : if true, if an exception occurs rethrow the error.
   * @return array A list of message IDs
   * @throws Exception
   */
  public function publish(string $topicName, $data, array $attributes, bool $jsonEncodeData=true, bool $raiseExceptionInCaseOfError=false):array
  {
    try
    {
      $topic = $this->pubSub->topic($topicName);

      $dataToPublish = $jsonEncodeData ? json_encode($data) : $data;

      $this->logger->debug("publishing to message to pubsub", ['topicName'=>$topicName, 'attributes'=>$attributes, 'jsonEncodeData'=>$jsonEncodeData, 'raiseExceptionInCaseOfError'=>$raiseExceptionInCaseOfError, 'data'=> $dataToPublish]);

      return $topic->publish([
        'data'       => $dataToPublish,
        'attributes' => $attributes
      ]);
    }
    catch(Exception $exception)
    {
      $this->logger->error("Error while publishing message on topic",
        [
          'topicName'                   =>$topicName,
          'attributes'                  =>$attributes,
          'jsonEncodeData'              =>$jsonEncodeData,
          'raiseExceptionInCaseOfError' =>$raiseExceptionInCaseOfError,
          'data'                        =>$data,
          Logger::$EXCEPTION            =>$exception
        ]);

      if($raiseExceptionInCaseOfError)
      {
        throw $exception;
      }
      return [];
    }
  }
}
