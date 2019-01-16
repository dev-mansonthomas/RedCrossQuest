<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 25/04/2017
 * Time: 00:03
 */

namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use Monolog\Logger;

class Entity
{
  protected $logger;
  /***
    @var string[]
   */
  protected $_fieldList
;
  public function __construct(Logger $logger)
  {
    $this->logger = $logger;
  }

  public function generateCSVHeader()
  {
    return implode(";", $this->_fieldList)."\n";
  }

  public function generateCSVRow()
  {
    $csvRow="";
    foreach($this->_fieldList as $field)
    {
      $csvRow.= $this->{$field}.";";
    }
    return $csvRow."\n";
  }


  /**
   * set on this object the property named $this->$key,  $data[$key] as an integer value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getInteger(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = $data[$key] === null ? null : (int)$data[$key];
    }
  }

  /**
   * set on this object the property named $this->$key,  $data[$key] as a float value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getFloat(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = $data[$key] === null ? null : (float) $data[$key];
    }
  }


  /**
   * set on this object the property named $this->$key,  $data[$key] as an string value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getString(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = $data[$key];
    }
  }


  /**
   * set on this object the property named $this->$key,  $data[$key] as an string value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   * @throws \Exception if json_decode throws an error
   */
  protected function getJson(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      try
      {
        $this->$key = json_decode($data[$key], true);
      }
      catch(\Exception $e)
      {
        $this->logger->addError("Error while decoding json for key '$key'", array("exception"=> $e, "data"=>$data));
        throw $e;
      }

    }
  }

  /**
   * set on this object the property named $this->$key,  $data[$key] as an boolean value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getBoolean(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      $value = $data[$key];

      if($value."" === "1" || $value."" === "true" || $value === true)
      {
        $this->$key = true;
      }
      else
      {
        $this->$key = false;
      }

    }
    else
    {
      $this->$key = false;
    }
  }
  /**
   * set on this object the property named $this->$key,  $data[$key] as a date value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   * @throws \Exception when failing to parse date
   */
  protected function getDate(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      if(is_array($data[$key]))
      {
        //$this->logger->addError("json parsed momentjs");
        // json parsed momentjs object : {"date":"2017-06-05 03:00:00.000000","timezone_type":3,"timezone":"Europe/Paris"}
        $array = $data[$key];
        try
        {
          $this->$key = Carbon::createFromFormat("Y-m-d H:i:s.u", $array['date'], $array['timezone']);
          $this->$key->setTimezone("UTC");
        }
        catch(\Exception $e)
        {
          $this->logger->addError("Error while decoding date (from momentjs date) for key '$key'", array("exception"=> $e, "data"=>$data));
          throw $e;
        }
      }
      else
      {
        $stringValue = $data[$key];
        if($stringValue != null)
        {
          if(strpos($stringValue, 'T') !== false)
          {
            //json javascript date : "2017-06-04T23:00:00.000Z"
            //$this->logger->addError("json javascript ".$stringValue);
            try
            {
              $this->$key = Carbon::parse($stringValue);
              //$this->logger->addError("json javascript parsed : ".$this->$key);
            }
            catch(\Exception $e)
            {
              $this->logger->addError("Error while decoding date (from js date) for key '$key'", array("exception"=> $e, "data"=>$data));
              throw $e;
            }

          }
          else
          {
            // from DB Date :"2016-06-09 00:36:43"
            //  The parsing is done with UTC timezone, as dates are stored with this timezone in DB
            //  Then we switch the date to Paris Timezone to reflect the Timezone of the client
            //$this->logger->addError("DB date '$stringValue'");
            try
            {
              $this->$key = Carbon::parse($stringValue, 'UTC')->setTimezone("Europe/Paris");

             // $this->logger->addError("DB date Carbon : ".$this->$key);
            }
            catch(\Exception $e)
            {
              $this->logger->addError("Error while decoding date (from DB date) for key '$key'", array("exception"=> $e, "data"=>$data));
              throw $e;
            }
          }
        }
      }
    }
  }
}
