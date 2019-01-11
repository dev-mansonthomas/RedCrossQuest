<?php
namespace RedCrossQuest\Entity;
use Monolog\Logger;

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
  public $time_to_reach     ;
  public $transport_to_reach;

  protected $_fieldList = ['id','ul_id','code','name','latitude','longitude','address','postal_code','city','max_people','advice','localization','minor_allowed','created','enabled','type','time_to_reach','transport_to_reach'];

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param Logger $logger
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, Logger $logger)
  {
    parent::__construct($logger);

    $this->getInteger('id'           , $data);
    $this->getInteger('ul_id'        , $data);
    $this->getString ('code'         , $data);
    $this->getString ('name'         , $data);
    $this->getFloat  ('latitude'     , $data);
    $this->getFloat  ('longitude'    , $data);
    $this->getString ('address'      , $data);
    $this->getString ('postal_code'  , $data);
    $this->getString ('city'         , $data);
    $this->getString ('max_people'   , $data);
    $this->getString ('advice'       , $data);
    $this->getString ('localization' , $data);
    $this->getBoolean('minor_allowed', $data);
    $this->getDate   ('created'      , $data);
    $this->getBoolean('enabled'      , $data);


    $this->getInteger( 'type'               , $data);
    $this->getInteger( 'time_to_reach'      , $data);
    $this->getInteger( 'transport_to_reach' , $data);

  }
}
