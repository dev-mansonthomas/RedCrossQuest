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
  protected function getString($key, $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = $data[$key];
    }
  }

  protected function getDate($key, $data)
  {
    if(array_key_exists($key, $data))
    {

      if(is_array($data[$key]))
      {//json parsing
        $this->logger->debug("Date from Javascript", $data[$key]);

//{"date":"2016-05-25 07:00:00.000000","timezone_type":3,"timezone":"Europe/Paris"}
        $array = $data[$key];
        $this->$key = Carbon::parse($array['date']);
        $this->$key->timezone = $array['timezone']  ;

      }
      else
      {//from DB
        $stringValue = $data[$key];
        if($stringValue != null)
        {
          $this->$key = Carbon::parse($stringValue);
        }
      }
    }
  }
}
