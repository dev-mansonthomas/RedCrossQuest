<?php
namespace RedCrossQuest\Entity;

use Google\Cloud\Logging\PsrLogger;

class UserEntity extends Entity
{
  public $id                          ;
  public $nivol                       ;
  public $queteur_id                  ;
  public $password                    ;
  public $password_defined            ; // when we don't need the password, but just to know if it's defined (queteurEdit)

  public $role                        ;

  public $created                     ;
  public $updated                     ;

  public $active                      ;
  public $last_failure_login_date     ;
  public $nb_of_failure               ;//since last successful login
  public $last_successful_login_date  ;

  public $init_password_date          ;

  public $first_name                  ;
  public $last_name                   ;










  protected $_fieldList = ['id','nivol','queteur_id','password','password_defined','role','created','updated','active','last_failure_login_date','nb_of_failure','last_successful_login_date','init_password_date','first_name','last_name'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param PsrLogger $logger
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, PsrLogger $logger)
  {
    parent::__construct($logger);
    $this->getInteger('id'                        , $data);
    $this->getString ('nivol'                     , $data, 20);
    $this->getInteger('queteur_id'                , $data);
    $this->getString ('password'                  , $data, 60);
    $this->getBoolean('password_defined'          , $data);


    $this->getInteger('role'                      , $data);
    $this->getDate   ('created'                   , $data);
    $this->getDate   ('updated'                   , $data);
    $this->getBoolean('active'                    , $data);
    $this->getDate   ('last_failure_login_date'   , $data);
    $this->getInteger('nb_of_failure'             , $data);
    $this->getDate   ('last_successful_login_date', $data);
    $this->getDate   ('init_password_date'        , $data);

    $this->getString ('first_name'                , $data, 100);
    $this->getString ('last_name'                 , $data, 100);
  }
}
