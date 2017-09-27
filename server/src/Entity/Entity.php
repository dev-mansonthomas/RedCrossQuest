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

  protected function getInteger($key, $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = (int)$data[$key];
    }
  }

  protected function getString($key, $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = $data[$key];
    }
  }

  protected function getBoolean($key, $data)
  {
    if(array_key_exists($key, $data))
    {
      $value = $data[$key];

      if($value."" === "1" || $value."" === "true")
        $this->$key = true;
      else
        $this->$key = false;
    }
    else
    {
      $this->$key = false;
    }
  }

  protected function getDate($key, $data)
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
            $this->$key = Carbon::parse($stringValue)->setTimezone("Europe/Paris");
          }
        }
      }
    }
  }
}
