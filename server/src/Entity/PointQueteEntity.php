<?php
namespace RedCrossQuest\Entity;

class PointQueteEntity  extends Entity
{
  public $id                ;
  public $ul_id             ;
  public $code              ;
  public $name              ;
  public $latitude          ;
  public $longitude         ;
  public $address           ;
  public $postal_code       ;
  public $city              ;
  public $max_people        ;
  public $advice            ;
  public $localization      ;
  public $minor_allowed     ;
  public $created           ;
  public $enabled           ;
  public $type              ;
  public $time_to_teach     ;
  public $transport_to_teach;

  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
  public function __construct($data)
  {
    $this->getInteger('id'           , $data);
    $this->getString ('ul_id'        , $data);
    $this->getString ('code'         , $data);
    $this->getString ('name'         , $data);
    $this->getString ('latitude'     , $data);
    $this->getString ('longitude'    , $data);
    $this->getString ('address'      , $data);
    $this->getString ('postal_code'  , $data);
    $this->getString ('city'         , $data);
    $this->getString ('max_people'   , $data);
    $this->getString ('advice'       , $data);
    $this->getString ('localization' , $data);
    $this->getString ('minor_allowed', $data);
    $this->getDate   ('created'      , $data);
    $this->getBoolean('enabled'     , $data);


    $this->getInteger( 'type'               , $data);
    $this->getInteger( 'time_to_teach'      , $data);
    $this->getInteger( 'transport_to_teach' , $data);

  }
}
