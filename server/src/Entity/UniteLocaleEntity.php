<?php
namespace RedCrossQuest\Entity;

use Monolog\Logger;

class UniteLocaleEntity  extends Entity
{
  public $id;
  public $name;
  public $phone;
  public $latitude;
  public $longitude;
  public $address;
  public $postal_code;
  public $city;
  public $external_id;
  public $email;
  public $id_structure_rattachement;
  public $date_demarrage_activite;
  public $date_demarrage_rcq;
  public $mode;
  public $publicDashboard;

  protected $_fieldList = ['id','name','phone','latitude','longitude','address','postal_code','city','external_id','email','id_structure_rattachement','date_demarrage_activite','date_demarrage_rcq','mode','publicDashboard'];
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
    $this->getInteger('id'                        , $data);
    $this->getString ('name'                      , $data, 50);
    $this->getString ('phone'                     , $data, 13);
    $this->getFloat  ('latitude'                  , $data);
    $this->getFloat  ('longitude'                 , $data);
    $this->getString ('address'                   , $data, 200);
    $this->getInteger('postal_code'               , $data);
    $this->getString ('city'                      , $data, 70);
    $this->getInteger('external_id'               , $data);
    $this->getEmail  ('email'                     , $data);
    $this->getInteger('id_structure_rattachement' , $data);
    $this->getDate   ('date_demarrage_activite'   , $data);
    $this->getDate   ('date_demarrage_rcq'        , $data);
    $this->getInteger('mode'                      , $data);
    $this->getString ('publicDashboard'           , $data, 100);
  }
}
