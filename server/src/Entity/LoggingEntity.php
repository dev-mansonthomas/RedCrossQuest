<?php


namespace RedCrossQuest\Entity;


use RedCrossQuest\Middleware\DecodedToken;

class LoggingEntity
{
  /**
   * @property array JWT decoded token as an associative array. It contains all the infos required to have the right context for the logging entry
   */
  private $decodedToken;

  /**
   * @property array Other information
   */
  private $otherData;

  /**
   * @property string path of the request
   */
  private $url;


  public function __construct(DecodedToken $decodedToken = null, array $otherData = array())
  {
    $this->decodedToken = isset($decodedToken) ? $decodedToken->toArray() : [];
    $this->otherData    = $otherData;
    $this->url          = $_SERVER["REQUEST_URI"];
  }
  //return this class as an associative array, otherData added if exists.
  public function loggingInfoArray()
  {
    return array_merge( $this->decodedToken, $this->otherData);
  }
}
