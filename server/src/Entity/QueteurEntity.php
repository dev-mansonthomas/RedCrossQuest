<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;

use Symfony\Component\Config\Definition\Exception\Exception;

class QueteurEntity
{
  public $id;
  public $email                       ;
  public $first_name                  ;
  public $last_name                   ;
  public $minor                       ;
  public $secteur                     ;
  public $nivol                       ;
  public $mobile                      ;
  public $created                     ;
  public $updated                     ;
  public $parent_authorization        ;
  public $temporary_volunteer_form    ;
  public $notes                       ;
  public $ul_id                       ;

  public $point_quete_id              ;
  public $point_quete_name            ;
  public $depart_theorique            ;
  public $depart                      ;
  public $retour                      ;

  public $active                      ;
  public $man                         ;
  public $birthdate                   ;
  public $qr_code_printed             ;
  public $referent_volunteer          ;

  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
  public function __construct($data)
  {
    $this->getString('id'                          , $data);
    $this->getString('email'                       , $data);
    $this->getString('first_name'                  , $data);
    $this->getString('last_name'                   , $data);
    $this->getString('minor'                       , $data);
    $this->getString('secteur'                     , $data);
    $this->getString('nivol'                       , $data);
    $this->getString('mobile'                      , $data);
    $this->getString('created'                     , $data);
    $this->getString('updated'                     , $data);
    $this->getString('parent_authorization'        , $data);
    $this->getString('temporary_volunteer_form'    , $data);
    $this->getString('notes'                       , $data);
    $this->getString('ul_id'                       , $data);

    $this->getString('point_quete_id'              , $data);
    $this->getString('point_quete_name'            , $data);
    $this->getString('depart_theorique'            , $data);
    $this->getDate  ('depart'                      , $data);
    $this->getDate  ('retour'                      , $data);

    $this->getString('active'                      , $data);
    $this->getString('man'                         , $data);
    $this->getDate  ('birthdate'                   , $data);

    $this->getString('qr_code_printed'             , $data);
    $this->getString('referent_volunteer'          , $data);

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
