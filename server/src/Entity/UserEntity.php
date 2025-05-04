<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use Exception;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="UserEntity", required={"nivol","queteur_id","password_defined","role","created","active","nb_of_failure"})
 */
class UserEntity extends Entity
{
  /**
   * @OA\Property()
   * @var int $id user Id
   */
  public int $id                          ;
  /**
   * @OA\Property()
   * @var string $nivol nivol of the user that is used as login with angularjs app. (Angular 8+ will use firebase and email)
   */
  public string $nivol                       ;
  /**
   * @OA\Property()
   * @var int $queteur_id Queteur ID (where name, email, mobile is stored)
   */
  public int $queteur_id                  ;
  /**
   * @OA\Property()
   * @var int $password hash of the use password
   */
  public int $password                    ;
  /**
   * @OA\Property()
   * @var boolean $password_defined true if the password has been defined. when we don't need the password, but just to know if it's defined (queteurEdit)
   */
  public bool $password_defined            ;
  /**
   * @OA\Property()
   * @var int $role Role of the user : {id:1,label:'Lecture Seule' },{id:2,label:'Opérateur'},{id:3,label:'Compteur'},{id:4,label:'Administrateur'}
   */

  public int $role                        ;
  /**
   * @OA\Property()
   * @var Carbon $created user creation date
   */

  public Carbon $created                     ;
  /**
   * @OA\Property()
   * @var Carbon $updated user last update date
   */
  public Carbon $updated                     ;
  /**
   * @OA\Property()
   * @var boolean $active is the user active or not
   */

  public bool $active                      ;
  /**
   * @OA\Property()
   * @var Carbon $last_failure_login_date last login failure
   */
  public Carbon $last_failure_login_date     ;
  /**
   * @OA\Property()
   * @var int $nb_of_failure number of failure since last login
   */
  public int $nb_of_failure               ;//since last successful login
  /**
   * @OA\Property()
   * @var Carbon $last_successful_login_date last successfull login date
   */
  public Carbon $last_successful_login_date  ;
  /**
   * @OA\Property()
   * @var int $init_passwd_date when the password was last re initiated
   */

  public int $init_passwd_date          ;
  /**
   * @OA\Property()
   * @var string $first_name first name of the user
   */

  public string $first_name                  ;
  /**
   * @OA\Property()
   * @var string $last_name last name of the user
   */
  public string $last_name                   ;

  protected array $_fieldList = ['id','nivol','queteur_id','password','password_defined','role','created','updated','active','last_failure_login_date','nb_of_failure','last_successful_login_date','init_passwd_date','first_name','last_name'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array &$data, LoggerInterface $logger)
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
    $this->getDate   ('init_passwd_date'          , $data);

    $this->getString ('first_name'                , $data, 100);
    $this->getString ('last_name'                 , $data, 100);
  }
}
