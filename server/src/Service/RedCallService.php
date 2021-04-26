<?php
namespace RedCrossQuest\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Created by IntelliJ IDEA.
 * User: thomas
 * Date: 08/11/2018
 * Time: 15:56
 */

class RedCallService
{
  protected array $settings;
  /** @var Logger */
  protected Logger $logger;
  /** @var Client */
  protected Client $client;

  protected string $redCallSecret;

  public function __construct(array $settings, string $redCallSecret, Logger $logger)
  {
    $this->settings      = $settings['RedCall'];
    $this->redCallSecret = $redCallSecret;
    $this->logger        = $logger;
    $this->client        = new Client(
      [
        // Base URI is used with relative requests
        'base_uri' => $this->settings['base_uri'],
        // You can set any number of default request options.
        'timeout'  => $this->settings['timeout'],
      ]
    );
  }

  private function hash($method, $uri, $body=""):string
  {

    $data = $method.$this->settings['base_uri'].$uri.$body;
    $hash = hash_hmac( $this->settings['hashing_algorithm'], $data, $this->redCallSecret, false);

    if(!$hash)
    {
      $this->logger->error("Error while computing HMAC Hash",
        [
          "algo"=>$this->settings['hashing_algorithm'],
          "data"=> $data
        ]);
      throw new Exception("Error while computing RedCall hash");
    }

    return $hash;
    
  }

  /**
   *
   * @param string $uri
   * @return array
   * @throws GuzzleException
   */
  public function get(string $uri):array
  {

    $hash = $this->hash("GET", $uri);

    $options = [ 'base_uri'  => $this->settings['base_uri'],
      'headers'   => [
        'Authorization' => "Bearer ".$this->settings['token'],
        'X-Signature'   => $hash,
        'Content-Type'  => 'application/json; charset=utf-8',
        'Accept'        => 'application/json'
      ]];

    $this->logger->debug("RedCall call - GET", ["uri"=>$uri,"options"=>$options]);

    try
    {
      $response = $this->client->request(
        'GET',
        $uri,
        $options);

      $decodedResponse = json_decode($response->getBody(), true );

      $this->logger->debug("RedCall Response" , ["response"=>$decodedResponse]);
      return $decodedResponse;
      
    }
    catch(Exception $exception)
    {
      $this->logger->error("Error while calling RedCall",
        [
          'uri'              => $uri,
          'hash'             => $hash ,
          Logger::$EXCEPTION => $exception
        ]);

      throw $exception;
    }
  }
}
