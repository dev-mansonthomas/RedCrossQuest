<?php
namespace RedCrossQuest\Entity;

use Monolog\Logger;

/**
 * @property \RedCrossQuest\Entity\UserEntity user
 * @property string referent_volunteerQueteur
 */
class QueteurEntity  extends Entity
{
  public $id;
  public $email                       ;
  public $first_name                  ;
  public $last_name                   ;
/**
"1">Action Sociale
"2">Secours
"3">Non Bénévole
"4">Ancien Bénévole, Inactif ou Adhérent
"5">Commerçant
"6">Special
 */
  public $secteur                     ;
  public $nivol                       ;
  public $mobile                      ;
  public $created                     ;
  public $updated                     ;
  public $parent_authorization        ;
  public $temporary_volunteer_form    ;
  public $notes                       ;
  public $ul_id                       ;
  public $ul_name                     ;
  public $ul_longitude                ;
  public $ul_latitude                 ;

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
  public $referent_volunteer_entity   ;

  public $anonymization_token         ;
  public $anonymization_date          ;

  protected $_fieldList = ['id','email','first_name','last_name','secteur','nivol','mobile','created','updated','parent_authorization','temporary_volunteer_form','notes','ul_id','ul_name','ul_longitude','ul_latitude','point_quete_id','point_quete_name','depart_theorique','depart','retour','active','man','birthdate','qr_code_printed','referent_volunteer','referent_volunteer_entity','anonymization_token','anonymization_date'];

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param Logger $logger the logger instance
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, Logger $logger)
  {
    parent::__construct($logger);

    $this->getString('id'                          , $data);
    $this->getString('email'                       , $data);
    $this->getString('first_name'                  , $data);
    $this->getString('last_name'                   , $data);
    $this->getString('secteur'                     , $data);
    $this->getString('nivol'                       , $data);
    $this->getString('mobile'                      , $data);
    $this->getString('created'                     , $data);
    $this->getString('updated'                     , $data);
    $this->getString('parent_authorization'        , $data);
    $this->getString('temporary_volunteer_form'    , $data);
    $this->getString('notes'                       , $data);
    $this->getString('ul_id'                       , $data);
    $this->getString('ul_name'                     , $data);
    $this->getString('ul_latitude'                 , $data);
    $this->getString('ul_longitude'                , $data);

    $this->getString('point_quete_id'              , $data);
    $this->getString('point_quete_name'            , $data);
    $this->getDate  ('depart_theorique'            , $data);
    $this->getDate  ('depart'                      , $data);
    $this->getDate  ('retour'                      , $data);

    $this->getString('active'                      , $data);
    $this->getString('man'                         , $data);
    $this->getDate  ('birthdate'                   , $data);

    $this->getString('qr_code_printed'             , $data);
    $this->getString('referent_volunteer'          , $data);

    $this->getString('anonymization_token'         , $data);
    $this->getDate  ('anonymization_date'          , $data);


  }
}
