<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;

class DailyStatsBeforeRCQEntity
{
  public $id           ;
  public $ul_id        ;
  public $date         ;
  public $amount       ;

  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
  public function __construct($data)
  {
    $this->getString('id'           , $data);
    $this->getString('ul_id'        , $data);
    $this->getDate  ('date'         , $data);
    $this->getString('amount'       , $data);

  }

  private function getString($key, $data)
  {
    if(array_key_exists($key, $data))
    {
      $this->$key = $data[$key];
    }
  }

  private function getDate($key, $data)
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
