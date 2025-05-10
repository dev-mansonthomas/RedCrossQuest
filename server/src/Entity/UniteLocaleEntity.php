<?php /** @noinspection SpellCheckingInspection */

namespace RedCrossQuest\Entity;


use Carbon\Carbon;
use Exception;
use OpenApi\Annotations as OA;
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
  public int $id;
  /**
   * @OA\Property()
   * @var string $name Name of the UL
   */
  public string $name;

  /**
   * @OA\Property()
   * @var string $phone phone to contact the UL
   */
  public string $phone;

  /**
   * @OA\Property()
   * @var float $latitude latitude of the base of the UL
   */
  public float $latitude;

  /**
   * @OA\Property()
   * @var float $longitude Longitude of the base of UL
   */
  public $longitude;
  /**
   * @OA\Property()
   * @var string $address Street number and name of the UL
   */
  public string $address;
  /**
   * @OA\Property()
   * @var string $postal_code Postal code of the UL
   */
  public string $postal_code;
  /**
   * @OA\Property()
   * @var string $city City of the UL
   */
  public string $city;
  /**
   * @OA\Property()
   * @var integer $external_id Id of the UL in the RedCross Ref
   */
  public int $external_id;
  /**
   * @OA\Property()
   * @var string $email email to contact the UL
   */
  public string $email;
  /**
   * @OA\Property()
   * @var integer $id_structure_rattachement ID of the parent structure of the UL
   */
  public int $id_structure_rattachement;
  /**
   * @OA\Property()
   * @var Carbon $date_demarrage_activite Date of creation of the UL
   */
  public Carbon $date_demarrage_activite;
  /**
   * @OA\Property()
   * @var Carbon $date_demarrage_rcq Date of the first use of RCQ
   */
  public Carbon $date_demarrage_rcq;
  /**
   * @OA\Property()
   * @var integer $mode Mode of use of RCQ. Might be deprecated.
   */
  public int $mode;
  /**
   * @OA\Property()
   * @var string $publicDashboard Which Spotfire public Dashboard is used. The one with the total amount (RCQ-Public-MontantsVisibles) or without (RCQ-Public-MontantsCachés)
   */
  public string $publicDashboard;
  /**
   * @OA\Property()
   * @var boolean $president_man Is the President a Man
   */
  public bool $president_man;
  /**
   * @OA\Property()
   * @var string $president_nivol Nivol of the president
   */
  public string $president_nivol;
  /**
   * @OA\Property()
   * @var string $president_first_name first name of the President
   */
  public string $president_first_name;
  /**
   * @OA\Property()
   * @var string $president_last_name last name of the President
   */
  public string $president_last_name;
  /**
   * @OA\Property()
   * @var string $president_email email of the President
   */
  public string $president_email;
  /**
   * @OA\Property()
   * @var string $president_mobile mobile of the President
   */
  public string $president_mobile;
  /**
   * @OA\Property()
   * @var boolean $tresorier_man Is the Treasurer a Man
   */
  public bool $tresorier_man;
  /**
   * @OA\Property()
   * @var string $tresorier_nivol Nivol of the treasurer
   */
  public string $tresorier_nivol;
  /**
   * @OA\Property()
   * @var string $tresorier_first_name first name of the Treasurer
   */
  public string $tresorier_first_name;
  /**
   * @OA\Property()
   * @var string $tresorier_last_name last name of the Treasurer
   */
  public string $tresorier_last_name;
  /**
   * @OA\Property()
   * @var string $tresorier_email email of the Treasurer
   */
  public string $tresorier_email;
  /**
   * @OA\Property()
   * @var string $tresorier_mobile mobile of the Treasurer
   */
  public string $tresorier_mobile;
  /**
   * @OA\Property()
   * @var boolean $admin_man Is the Admin a Man
   */
  public bool $admin_man;
  /**
   * @OA\Property()
   * @var string $admin_nivol Nivol of the admin
   */
  public string $admin_nivol;
  /**
   * @OA\Property()
   * @var string $admin_first_name first name of the Admin
   */
  public string $admin_first_name;
  /**
   * @OA\Property()
   * @var string $admin_last_name last name of the Admin
   */
  public string $admin_last_name;
  /**
   * @OA\Property()
   * @var string $admin_email email of the Admin
   */
  public string $admin_email;
  /**
   * @OA\Property()
   * @var string $admin_mobile mobile of the Admin
   */
  public string $admin_mobile;

  /***UL REGISTRATION***/

  /**
   * @OA\Property()
   * @var int|null $registration_id the registration id
   */
  public ?int $registration_id;

  /**
   * @OA\Property()
   * @var string|null $registration_token the registration token, sent to the president by email, must be passed back to the form to get the validation.
   */
  public ?string $registration_token;

  /**
   * @OA\Property()
   * @var Carbon $created the registration create date
   */
  public Carbon $created;

  /**
   * @OA\Property()
   * @var bool $registration_approved if the approval is not done (null), approved or rejected
   */
  public bool $registration_approved;

  /**
   * @OA\Property()
   * @var string $reject_reason the reason of rejection
   */
  public string $reject_reason;

  /**
   * @OA\Property()
   * @var Carbon $approval_date Date of approval/rejection
   */
  public Carbon $approval_date;

  /**
   * @OA\Property()
   * @var int|null $registration_in_progress The value is 36 if a registration is in progress (lenght of the registration token) or null otherwise
   */
  public ?int $registration_in_progress;

  /**
   * @OA\Property()
   * @var string $tresorier_spreadsheet_id The Google Spreadsheet ID that is being updated with troncQueteurData
   */
  public string $tresorier_spreadsheet_id;



  protected array $_fieldList = ['id','name','phone','latitude','longitude','address','postal_code','city','external_id','email','id_structure_rattachement','date_demarrage_activite','date_demarrage_rcq','mode','publicDashboard',
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
    'registration_token'    ,
    'created'               ,
    'registration_approved' ,
    'reject_reason'         ,
    'approval_date'         ,
    'registration_in_progress',
    'tresorier_spreadsheet_id'];

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @param bool $lightMode light mode is used when searching UL for registration
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array &$data, LoggerInterface $logger, bool $lightMode=false)
  {
    parent::__construct($logger);
    $this->getInteger('id'                        , $data);
    $this->getString ('name'                      , $data, 50);
    $this->getInteger('postal_code'               , $data);
    $this->getString ('city'                      , $data, 70);
    $this->getInteger('registration_in_progress'  , $data);
    $this->getInteger('registration_id'           , $data);

    if($lightMode)
    {  //remove unncessary properties : lighter payload, avoid exposing object structure to public
      unset($this->phone                     );
      unset($this->latitude                  );
      unset($this->longitude                 );
      unset($this->address                   );
      unset($this->external_id               );
      unset($this->email                     );
      unset($this->id_structure_rattachement );
      unset($this->date_demarrage_activite   );
      unset($this->date_demarrage_rcq        );
      unset($this->mode                      );
      unset($this->publicDashboard           );
      unset($this->president_man             );
      unset($this->president_nivol           );
      unset($this->president_first_name      );
      unset($this->president_last_name       );
      unset($this->president_email           );
      unset($this->president_mobile          );
      unset($this->tresorier_man             );
      unset($this->tresorier_nivol           );
      unset($this->tresorier_first_name      );
      unset($this->tresorier_last_name       );
      unset($this->tresorier_email           );
      unset($this->tresorier_mobile          );
      unset($this->admin_man                 );
      unset($this->admin_nivol               );
      unset($this->admin_first_name          );
      unset($this->admin_last_name           );
      unset($this->admin_email               );
      unset($this->admin_mobile              );
      unset($this->registration_token        );
      unset($this->registration_approved     );
      unset($this->created                   );
      unset($this->approval_date             );
      unset($this->reject_reason             );

      return;
    }


    $this->getString ('phone'                     , $data, 13);
    $this->getFloat  ('latitude'                  , $data);
    $this->getFloat  ('longitude'                 , $data);
    $this->getString ('address'                   , $data, 200);
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

    $this->getString ('registration_token'         , $data, 36);
    $this->getBoolean('registration_approved'      , $data);
    $this->getDate   ('created'                    , $data);
    $this->getDate   ('approval_date'              , $data);
    $this->getString ('reject_reason'              , $data, 200);
    $this->getString ('tresorier_spreadsheet_id'   , $data, 200);
  }
}
