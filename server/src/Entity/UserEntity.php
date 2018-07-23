<?php
namespace RedCrossQuest\Entity;

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


  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data)
  {
    $this->getString ('id'                        , $data);
    $this->getString ('nivol'                     , $data);
    $this->getString ('queteur_id'                , $data);
    $this->getString ('password'                  , $data);
    $this->getBoolean('password_defined'          , $data);


    $this->getString ('role'                      , $data);
    $this->getDate   ('created'                   , $data);
    $this->getDate   ('updated'                   , $data);
    $this->getString ('active'                    , $data);
    $this->getDate   ('last_failure_login_date'   , $data);
    $this->getString ('nb_of_failure'             , $data);
    $this->getDate   ('last_successful_login_date', $data);
    $this->getDate   ('init_password_date'        , $data);

    $this->getString ('first_name'                , $data);
    $this->getString ('last_name'                 , $data);
  }


}
