<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 25/04/2017
 * Time: 00:03
 */

namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;

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
    $this->logger               = $logger;
    $this->clientInputValidator = new ClientInputValidator($logger);
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
      $value = $data[$key];
      //$this->logger->error("integer '$key'=>'$value' ".gettype($data[$key]));

      $this->$key = "$value"=="0"? 0 : $this->clientInputValidator->validateInteger($key, $data[$key], 100000000, false, null);
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
      $value = $data[$key];
      if(strlen($value) > 20)  // latitude, longitude(18,15) note: 12345.678, you will set the Datatype to DOUBLE(8, 3) where 8 is the total no. of digits excluding the decimal point, and 3 is the no. of digits to follow the decimal.
      {
        throw new \InvalidArgumentException("Invalid float value" .json_encode(['key'=>$key, 'value'=>$value]) );
      }

      $this->$key = $data[$key] === null ? null : (float) $data[$key];
    }
  }


  /**
   * set on this object the property named $this->$key,  $data[$key] as an string value
   * @param string $key       the key of the data to be returned
   * @param array  $data      the associative array
   * @param int    $maxSize   the max acceptable length of the string
   */
  protected function getString(string $key, array $data, int $maxSize)
  {
    if(array_key_exists($key, $data))
    {
      $value = $this->clientInputValidator->validateString($key, $data[$key], $maxSize , false );
      $this->$key = $value == null ? '' : $value;
    }
  }


  /**
   * set on this object the property named $this->$key,  $data[$key] as an string value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getEmail(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = $this->clientInputValidator->validateString($key, $data[$key], 100 , false , ClientInputValidator::$EMAIL_VALIDATION);
    }
  }

  //

  /**
   * set on this object the property named $this->$key,  $data[$key] as an string value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   * @param int    $maxSize   the max acceptable length of the string
   * @throws \Exception if json_decode throws an error
   */
  protected function getJson(string $key, array $data, int $maxSize)
  {
    if(array_key_exists($key, $data))
    {
      $value = $this->clientInputValidator->validateString($key, $data[$key], $maxSize , false );

      try
      {
        $this->$key = json_decode($value, true);
      }
      catch(\Exception $e)
      {
        $this->logger->error("Error while decoding json for key '$key'", array("exception"=> $e, "data"=>$data));
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
      $this->$key = $this->clientInputValidator->validateBoolean($key, $data[$key]."", false, false );
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
        //$this->logger->error("json parsed momentjs");
        // json parsed momentjs object : {"date":"2017-06-05 03:00:00.000000","timezone_type":3,"timezone":"Europe/Paris"}
        $array = $data[$key];
        try
        {
          $this->$key = Carbon::createFromFormat("Y-m-d H:i:s.u", $array['date'], $array['timezone']);
          $this->$key->setTimezone("UTC");
        }
        catch(\Exception $e)
        {
          $this->logger->error("Error while decoding date (from momentjs date) for key '$key'", array("exception"=> $e, "data"=>$data));
          throw $e;
        }
      }
      else
      {
        $stringValue = $data[$key];

        if(strlen($stringValue) > 25)
        {
          throw new \InvalidArgumentException("Invalid DATE value, length higher than the max permitted size " .json_encode(['key'=>$key, 'value'=>$stringValue, 'maxSize'=>25]) );
        }

        if($stringValue != null)
        {
          if(strpos($stringValue, 'T') !== false)
          {
            //json javascript date : "2017-06-04T23:00:00.000Z"
            //$this->logger->error("json javascript ".$stringValue);
            try
            {
              $this->$key = Carbon::parse($stringValue);
              //$this->logger->error("json javascript parsed : ".$this->$key);
            }
            catch(\Exception $e)
            {
              $this->logger->error("Error while decoding date (from js date) for key '$key'", array("exception"=> $e, "data"=>$data));
              throw $e;
            }

          }
          else
          {
            // from DB Date :"2016-06-09 00:36:43"
            //  The parsing is done with UTC timezone, as dates are stored with this timezone in DB
            //  Then we switch the date to Paris Timezone to reflect the Timezone of the client
            //$this->logger->error("DB date '$stringValue'");
            try
            {
              $this->$key = Carbon::parse($stringValue, 'UTC')->setTimezone("Europe/Paris");

             // $this->logger->error("DB date Carbon : ".$this->$key);
            }
            catch(\Exception $e)
            {
              $this->logger->error("Error while decoding date (from DB date) for key '$key'", array("exception"=> $e, "data"=>$data));
              throw $e;
            }
          }
        }
      }
    }
  }
}
