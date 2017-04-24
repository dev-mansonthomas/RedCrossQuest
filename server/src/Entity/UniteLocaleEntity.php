<?php
namespace RedCrossQuest\Entity;

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


  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   */
  public function __construct($data)
  {
    $this->getString('id'                        , $data);
    $this->getString('name'                      , $data);
    $this->getString('phone'                     , $data);
    $this->getString('latitude'                  , $data);
    $this->getString('longitude'                 , $data);
    $this->getString('address'                   , $data);
    $this->getString('postal_code'               , $data);
    $this->getString('city'                      , $data);
    $this->getString('external_id'               , $data);
    $this->getString('email'                     , $data);
    $this->getString('id_structure_rattachement' , $data);
    $this->getDate  ('date_demarrage_activite'   , $data);
    $this->getDate  ('date_demarrage_rcq'        , $data);
  }
}
