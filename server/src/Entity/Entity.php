<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 25/04/2017
 * Time: 00:03
 */

namespace RedCrossQuest\Entity;

use Carbon\Carbon;


class Entity
{

  /**
   * set on this object the property named $this->$key,  $data[$key] as an integer value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getInteger(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = (int)$data[$key];
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
   * set on this object the property named $this->$key,  $data[$key] as an boolean value
   * @param string $key the key of the data to be returned
   * @param array  $data the associative array
   */
  protected function getBoolean(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      $value = $data[$key];

      if($value."" === "1" || $value."" === "true")
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
   */
  protected function getDate(string $key, array $data)
  {
    if(array_key_exists($key, $data))
    {
      if(is_array($data[$key]))
      {
        // json parsed momentjs object : {"date":"2017-06-05 03:00:00.000000","timezone_type":3,"timezone":"Europe/Paris"}
        $array = $data[$key];
        $this->$key = Carbon::createFromFormat("Y-m-d H:i:s.u", $array['date'], $array['timezone']);
        $this->$key->setTimezone("UTC");
      }
      else
      {
        $stringValue = $data[$key];
        if($stringValue != null)
        {
          if(strpos($stringValue, 'T') !== false)
          {
            //json javascript date : "2017-06-04T23:00:00.000Z"
            $this->$key = Carbon::parse($stringValue);
          }
          else
          {
            // from DB Date :"2016-06-09 00:36:43"
            //  The parsing is done with UTC timezone, as dates are stored with this timezone in DB
            //  Then we switch the date to Paris Timezone to reflect the Timezone of the client
            $this->$key = Carbon::parse($stringValue, 'UTC')->setTimezone("Europe/Paris");

          }
        }
      }
    }
  }
}
