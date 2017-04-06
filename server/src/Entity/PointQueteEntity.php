<?php
namespace RedCrossQuest\Entity;

class PointQueteEntity
{
  public $id           ;
  public $ul_id        ;
  public $code         ;
  public $name         ;
  public $latitude     ;
  public $longitude    ;
  public $address      ;
  public $postal_code  ;
  public $city         ;
  public $max_people   ;
  public $advice       ;
  public $localization ;
  public $minor_allowed;
  public $created      ;

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
    $this->getString('code'         , $data);
    $this->getString('name'         , $data);
    $this->getString('latitude'     , $data);
    $this->getString('longitude'    , $data);
    $this->getString('address'      , $data);
    $this->getString('postal_code'  , $data);
    $this->getString('city'         , $data);
    $this->getString('max_people'   , $data);
    $this->getString('advice'       , $data);
    $this->getString('localization' , $data);
    $this->getString('minor_allowed', $data);
    $this->getDate  ('created'      , $data);
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
      $stringValue = $data[$key];
      $this->$key = date_parse($stringValue);
    }
  }
}
