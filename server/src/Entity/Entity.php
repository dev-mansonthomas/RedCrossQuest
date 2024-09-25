<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 25/04/2017
 * Time: 00:03
 */

namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;
use Throwable;

class Entity
{
  /**
   * @var LoggerInterface
   */
  protected LoggerInterface $logger;
  /**
   * @var ClientInputValidator
   */
  protected ClientInputValidator $clientInputValidator;


  /***
    @var string[]
   */
  protected array $_fieldList;

  public function getFieldList():array
  {
    return $this->_fieldList;
  }

  public function __construct(LoggerInterface $logger)
  {
    $this->logger               = $logger;
    $this->clientInputValidator = new ClientInputValidator($logger);
  }

  /**
   * @return string The CSV header based on the _fieldList array
   */
  public function generateCSVHeader():string
  {
    return implode(";", $this->_fieldList). PHP_EOL;
  }

  /**
   * @return string The CSV row based on the _fieldList array
   */
  public function generateCSVRow():string
  {
    $csvRow="";
    foreach($this->_fieldList as $field)
    {
      try
      {
        $value = $this->{$field};
        if(gettype($value) ==='boolean')
        {
          $csvRow.= ($value?1:0).";";
        }
        else
        {
          if(is_array($value))
          {
            $csvRow.= implode("-",$value).";";
          }
          else
          {
            $csvRow.= $value.";";
          }

        }
      }
      catch(Throwable $e)
      {
        $this->logger->error("error while serializing as CSV",["name"=>$field, "value"=>$value, "throwable"=>print_r($e,true), "entity"=>print_r($entity, true)]);
      }
    }
    return $csvRow. PHP_EOL;
  }

  /**
   * @return array an associative array, with the field name as key and its value as value
   */
  public function prepareDataForFirestoreUpdate():array
  {
    $data = [];

    foreach($this->_fieldList as $key)
    {
      $data[$key]=$this->$key;
    }

    return $data;
  }

  /**
   * to reduce PubSub message payload, keys that have null values are deleted
   */
  public function genericPreparePubSubPublishing():void
  {
    foreach($this->_fieldList as $key)
    {                                                                         //TroncQueteurEntity.don_cb_details is an array
      if(empty($this->$key) || !isset($this->$key) || is_null($this->$key) || (!is_array($this->$key) && !is_object($this->$key) && $this->$key."" === "null"))
        unset($this->$key);
    }
  }

  /**
   * set on this object the property named $this->$key,  $data[$key] as an integer value
   * @param string $key the key of the data to be returned
   * @param array|null $data the associative array
   */
  protected function getInteger(string $key, ?array &$data, int $defaultValue=null):void
  {
    $this->$key = $this->clientInputValidator->validateInteger($key, $data, 100000000, false, $defaultValue);
  }

  /**
   * set on this object the property named $this->$key,  $data[$key] as a float value
   * @param string $key the key of the data to be returned
   * @param array|null $data the associative array
   */
  protected function getFloat(string $key, ?array &$data):void
  {
    if($data != null && array_key_exists($key, $data))
    {
      $value = $data[$key];
      
      if($value === null)
        $this->$key = null;
      else
      {
        # ?? '' : if $value is null ==> pass empty string, which is ok for this case.
        if(strlen($value) > 20)  // latitude, longitude(18,15) note: 12345.678, you will set the Datatype to DOUBLE(8, 3) where 8 is the total no. of digits excluding the decimal point, and 3 is the no. of digits to follow the decimal.
        {
          throw new InvalidArgumentException("Invalid float value" .json_encode(['key'=>$key, 'value'=>$value]) );
        }
        $this->$key = (float) $value;
      }
    }
  }


  /**
   * set on this object the property named $this->$key,  $data[$key] as an string value
   * @param string $key       the key of the data to be returned
   * @param array  $data      the associative array
   * @param int    $maxSize   the max acceptable length of the string
   */
  protected function getString(string $key, array &$data, int $maxSize):void
  {
    $value = $this->clientInputValidator->validateString($key, $data, $maxSize , false );
    $this->$key = $value == null ? '' : $value;
  }


  /**
   * set on this object the property named $this->$key,  $data[$key] as an string value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getEmail(string $key, array &$data):void
  {
    $this->$key = $this->clientInputValidator->validateString($key, $data, 255 , false , ClientInputValidator::$EMAIL_VALIDATION);
  }

  //

  /**
   * set on this object the property named $this->$key,  $data[$key] as an string value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   * @param int    $maxSize   the max acceptable length of the string
   * @throws Exception if json_decode throws an error
   */
  protected function getJson(string $key, array &$data, int $maxSize):void
  {
    $value = $this->clientInputValidator->validateString($key, $data, $maxSize , false );
    try
    {
      $this->$key = json_decode($value, true);
    }
    catch(Exception $e)
    {
      $this->logger->error("Error while decoding json for key '$key'", array("data"=>$data, Logger::$EXCEPTION=> $e));
      throw $e;
    }
  }

  /**
   * set on this object the property named $this->$key,  $data[$key] as an boolean value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getBoolean(string $key, array &$data):void
  {
    $this->$key = $this->clientInputValidator->validateBoolean($key, $data, false, false );
  }
  /**
   * set on this object the property named $this->$key,  $data[$key] as a date value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   * @throws Exception when failing to parse date
   */
  protected function getDate(string $key, array &$data):void
  {
    if(array_key_exists($key, $data))
    {
      if(is_array($data[$key]))
      {
        // json parsed momentjs object : {"date":"2017-06-05 03:00:00.000000","timezone_type":3,"timezone":"Europe/Paris"}

        $array = $data[$key];
        try
        {

          //$this->logger->error("Entity->getDate() 1", ['date'=>$array['date'], "timezone"=>$array['timezone']]);
          $this->$key = Carbon::createFromFormat("Y-m-d H:i:s.u", $array['date'], $array['timezone']);
          //$this->logger->error("Entity->getDate() 2", ['date'=>$array['date'], "timezone"=>$array['timezone'], "DateBeforeSettingUTC"=>$this->$key]);
          $this->$key->setTimezone("UTC");
          //$this->logger->error("Entity->getDate() 3", ['date'=>$array['date'], "timezone"=>$array['timezone'], "DateAfterSettingUTC"=>$this->$key]);
        }
        catch(Exception $e)
        {
          $this->logger->error("Error while decoding date (from momentjs date) for key '$key'", array("data"=>$data, Logger::$EXCEPTION=> $e));
          throw $e;
        }
      }
      else
      {
        $stringValue = $data[$key];

        if($stringValue != null)
        {
          if(strlen($stringValue) > 27)
          {
            throw new InvalidArgumentException("Invalid DATE value, length higher than the max permitted size " .json_encode(['key'=>$key, 'value'=>$stringValue, 'maxSize'=>27]) );
          }

          if(str_contains($stringValue, 'T'))
          {
            //json javascript date : "2017-06-04T23:00:00.000Z"
            //                        2019-11-05T21:51:21.000000Z
            //$this->logger->debug("json javascript ".$stringValue);
            try
            {
              $this->$key = Carbon::parse($stringValue);
             // $this->$key->setTimezone("UTC");
              //$this->logger->debug("json javascript parsed for '$key' : '".$this->$key."' stringValue='$stringValue");
            }
            catch(Exception $e)
            {
              $this->logger->error("Error while decoding date (from js date) for key '$key'", array("data"=>$data, Logger::$EXCEPTION=> $e));
              throw $e;
            }

          }
          else
          {
            // from DB Date :"2016-06-09 00:36:43"
            //  The parsing is done with UTC timezone, as dates are stored with this timezone in DB
            //  Then we switch the date to Paris Timezone to reflect the Timezone of the client
            //$this->logger->debug("DB date '$stringValue'");
            try
            {
              $this->$key = Carbon::parse($stringValue, 'UTC')->setTimezone("Europe/Paris");

             // $this->logger->debug("DB date Carbon : ".$this->$key);
            }
            catch(Exception $e)
            {
              $this->logger->error("Error while decoding date (from DB date) for key '$key'", array("data"=>$data,Logger::$EXCEPTION=> $e));
              throw $e;
            }
          }
        }
      }
    }
    //fix "Typed property RedCrossQuestEntityTroncEntity::$depart must not be accessed before initialization"
    //if(!isset($this->$key))
    //{
      //Fix the following error, but gives a bad default value. Typed property RedCrossQuestEntityTroncEntity::$depart must be an instance of CarbonCarbon, null used
      //$this->$key = null;
      //this allows the GDRP export data to work, but gives an incorrect value, which is likely to have a wide set of side effects
      //$this->$key = Carbon::minValue();

    //}
  }
}
