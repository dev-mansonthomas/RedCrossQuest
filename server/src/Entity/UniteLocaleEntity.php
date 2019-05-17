<?php
namespace RedCrossQuest\Entity;


use RedCrossQuest\Service\Logger;

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

  public $president_man;
  public $president_nivol;
  public $president_first_name;
  public $president_last_name;
  public $president_email;
  public $president_mobile;
  public $tresorier_man;
  public $tresorier_nivol;
  public $tresorier_first_name;
  public $tresorier_last_name;
  public $tresorier_email;
  public $tresorier_mobile;
  public $admin_man;
  public $admin_nivol;
  public $admin_first_name;
  public $admin_last_name;
  public $admin_email;
  public $admin_mobile;

  protected $_fieldList = ['id','name','phone','latitude','longitude','address','postal_code','city','external_id','email','id_structure_rattachement','date_demarrage_activite','date_demarrage_rcq','mode','publicDashboard',
    'president_man'         ,
    'president_nivol'       ,
    'president_first_name'  ,
    'president_last_name'   ,
    'president_email'       ,
    'president_mobile'      ,
    'tresorier_man'         ,
    'tresorier_nivol'       ,
    'tresorier_first_name'  ,
    'tresorier_last_name'   ,
    'tresorier_email'       ,
    'tresorier_mobile'      ,
    'admin_man'             ,
    'admin_nivol'           ,
    'admin_first_name'      ,
    'admin_last_name'       ,
    'admin_email'           ,
    'admin_mobile'         ];
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

    $this->getBoolean('president_man'              , $data);
    $this->getString ('president_nivol'            , $data, 15);
    $this->getString ('president_first_name'       , $data, 100);
    $this->getString ('president_last_name'        , $data, 100);
    $this->getString ('president_email'            , $data, 100);
    $this->getString ('president_mobile'           , $data, 20);
    $this->getBoolean('tresorier_man'              , $data);
    $this->getString ('tresorier_nivol'            , $data, 15);
    $this->getString ('tresorier_first_name'       , $data, 100);
    $this->getString ('tresorier_last_name'        , $data, 100);
    $this->getString ('tresorier_email'            , $data, 100);
    $this->getString ('tresorier_mobile'           , $data, 20);
    $this->getBoolean('admin_man'                  , $data);
    $this->getString ('admin_nivol'                , $data, 15);
    $this->getString ('admin_first_name'           , $data, 100);
    $this->getString ('admin_last_name'            , $data, 100);
    $this->getString ('admin_email'                , $data, 100);
    $this->getString ('admin_mobile'               , $data, 20);
  }
}
