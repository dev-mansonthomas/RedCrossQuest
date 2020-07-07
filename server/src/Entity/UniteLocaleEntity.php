<?php /** @noinspection SpellCheckingInspection */

namespace RedCrossQuest\Entity;


use Carbon\Carbon;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="UniteLocaleEntity", required={"name","phone","latitude","longitude","address","postal_code","city","external_id","email","id_structure_rattachement","date_demarrage_activite",})
 */

class UniteLocaleEntity  extends Entity
{
  /**
   * @OA\Property()
   * @var int $id UL ID
   */
  public $id;
  /**
   * @OA\Property()
   * @var string $name Name of the UL
   */
  public $name;

  /**
   * @OA\Property()
   * @var string $phone phone to contact the UL
   */
  public $phone;

  /**
   * @OA\Property()
   * @var float $latitude latitude of the base of the UL
   */
  public $latitude;

  /**
   * @OA\Property()
   * @var float $longitude Longitude of the base of UL
   */
  public $longitude;
  /**
   * @OA\Property()
   * @var string $address Street number and name of the UL
   */
  public $address;
  /**
   * @OA\Property()
   * @var string $postal_code Postal code of the UL
   */
  public $postal_code;
  /**
   * @OA\Property()
   * @var string $city City of the UL
   */
  public $city;
  /**
   * @OA\Property()
   * @var integer $external_id Id of the UL in the RedCross Ref
   */
  public $external_id;
  /**
   * @OA\Property()
   * @var string $email email to contact the UL
   */
  public $email;
  /**
   * @OA\Property()
   * @var integer $id_structure_rattachement ID of the parent structure of the UL
   */
  public $id_structure_rattachement;
  /**
   * @OA\Property()
   * @var Carbon $date_demarrage_activite Date of creation of the UL
   */
  public $date_demarrage_activite;
  /**
   * @OA\Property()
   * @var Carbon $date_demarrage_rcq Date of the first use of RCQ
   */
  public $date_demarrage_rcq;
  /**
   * @OA\Property()
   * @var integer $mode Mode of use of RCQ. Might be deprecated.
   */
  public $mode;
  /**
   * @OA\Property()
   * @var string $publicDashboard Which Spotfire public Dashboard is used. The one with the total amount (RCQ-Public-MontantsVisibles) or without (RCQ-Public-MontantsCachés)
   */
  public $publicDashboard;
  /**
   * @OA\Property()
   * @var boolean $president_man Is the President a Man
   */
  public $president_man;
  /**
   * @OA\Property()
   * @var string $president_nivol Nivol of the president
   */
  public $president_nivol;
  /**
   * @OA\Property()
   * @var string $president_first_name first name of the President
   */
  public $president_first_name;
  /**
   * @OA\Property()
   * @var string $president_last_name last name of the President
   */
  public $president_last_name;
  /**
   * @OA\Property()
   * @var string $president_email email of the President
   */
  public $president_email;
  /**
   * @OA\Property()
   * @var string $president_mobile mobile of the President
   */
  public $president_mobile;
  /**
   * @OA\Property()
   * @var boolean $tresorier_man Is the Treasurer a Man
   */
  public $tresorier_man;
  /**
   * @OA\Property()
   * @var string $tresorier_nivol Nivol of the treasurer
   */
  public $tresorier_nivol;
  /**
   * @OA\Property()
   * @var string $tresorier_first_name first name of the Treasurer
   */
  public $tresorier_first_name;
  /**
   * @OA\Property()
   * @var string $tresorier_last_name last name of the Treasurer
   */
  public $tresorier_last_name;
  /**
   * @OA\Property()
   * @var string $tresorier_email email of the Treasurer
   */
  public $tresorier_email;
  /**
   * @OA\Property()
   * @var string $tresorier_mobile mobile of the Treasurer
   */
  public $tresorier_mobile;
  /**
   * @OA\Property()
   * @var boolean $admin_man Is the Admin a Man
   */
  public $admin_man;
  /**
   * @OA\Property()
   * @var string $admin_nivol Nivol of the admin
   */
  public $admin_nivol;
  /**
   * @OA\Property()
   * @var string $admin_first_name first name of the Admin
   */
  public $admin_first_name;
  /**
   * @OA\Property()
   * @var string $admin_last_name last name of the Admin
   */
  public $admin_last_name;
  /**
   * @OA\Property()
   * @var string $admin_email email of the Admin
   */
  public $admin_email;
  /**
   * @OA\Property()
   * @var string $admin_mobile mobile of the Admin
   */
  public $admin_mobile;

  /***UL REGISTRATION***/

  /**
   * @OA\Property()
   * @var int $registration_id the registration id
   */
  public $registration_id;

  /**
   * @OA\Property()
   * @var Carbon $created the registration create date
   */
  public $created;

  /**
   * @OA\Property()
   * @var bool $registration_approved if the approval is not done (null), approved or rejected
   */
  public $registration_approved;

  /**
   * @OA\Property()
   * @var string $reject_reason the reason of rejection
   */
  public $reject_reason;

  /**
   * @OA\Property()
   * @var Carbon $approval_date Date of approval/rejection
   */
  public $approval_date;


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
    'admin_mobile'          ,
    'registration_id'       ,
    'created'               ,
    'registration_approved' ,
    'reject_reason'         ,
    'approval_date'        ];
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

    $this->getInteger('registration_id'            , $data);
    $this->getBoolean('registration_approved'      , $data);
    $this->getDate   ('created'                    , $data);
    $this->getDate   ('approval_date'              , $data);
    $this->getString ('reject_reason'              , $data, 200);



  }
}
