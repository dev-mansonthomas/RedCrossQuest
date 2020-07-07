<?php
namespace RedCrossQuest\Entity;


use Carbon\Carbon;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="QueteurEntity", required={"email","first_name","last_name","secteur","mobile","created","ul_id","active","man","birthdate"})
 */
class QueteurEntity  extends Entity
{
  /**
   * @OA\Property()
   * @var int $id queteur Id
   */
  public $id;
  /**
   * @OA\Property()
   * @var string $email email of the queteur
   */
  public $email                       ;
  /**
   * @OA\Property()
   * @var string $first_name first name of the queteur
   */
  public $first_name                  ;
  /**
   * @OA\Property()
   * @var string $last_name last name of the queteur
   */
  public $last_name                   ;

  /**
   * @OA\Property()
   * @var int $secteur id of the secteur : "1">Action Sociale  "2">Secours "3">Non Bénévole "4">Ancien Bénévole, Inactif ou Adhérent "5">Commerçant "6">Special
   */
  public $secteur                     ;
  /**
   * @OA\Property()
   * @var string $nivol NIVOL of the queteur (Business ID for red cross volunteer)
   */
  public $nivol                       ;
  /**
   * @OA\Property()
   * @var string $mobile mobile phone of the queteur (starts with 336 or 337)
   */
  public $mobile                      ;
  /**
   * @OA\Property()
   * @var Carbon $created queteur creation date
   */
  public $created                     ;
  /**
   * @OA\Property()
   * @var Carbon $updated queteur last update date
   */
  public $updated                     ;
  /**
   * @OA\Property()
   * @deprecated
   * @var string $notes notes about the queteur (deprecated). Originally target to describe food allergy, and specifics about the queteur. But the RGPD risk (health data, bad usage of free text field), made the Red Cross to ask to remove this field.
   */
  public $notes                       ;
  /**
   * @OA\Property()
   * @var int $ul_id Id of the UL to which the queteur belongs
   */
  public $ul_id                       ;
  /**
   * @OA\Property()
   * @var string $ul_name Name of the UL to which the queteur belongs
   */
  public $ul_name                     ;
  /**
   * @OA\Property()
   * @var float $ul_longitude Longitude of the UL
   */
  public $ul_longitude                ;
  /**
   * @OA\Property()
   * @var float $ul_latitude Latitude of the UL
   */
  public $ul_latitude                 ;

  /**
   * @OA\Property()
   * @var int $point_quete_id Current Point De Quete ID  (when searching queteur, search can be perform by status (about to leave, on the street, returned))
   */
  public $point_quete_id              ;
  /**
   * @OA\Property()
   * @var string $point_quete_name  Current Point De Quete name   (when searching queteur, search can be perform by status (about to leave, on the street, returned))
   */
  public $point_quete_name            ;
  /**
   * @OA\Property()
   * @var Carbon $depart_theorique Theoretical Start date of going on the streets    (when searching queteur, search can be perform by status (about to leave, on the street, returned))
   */
  public $depart_theorique            ;
  /**
   * @OA\Property()
   * @var Carbon $depart Real start date of going on the streets.  (when searching queteur, search can be perform by status (about to leave, on the street, returned))
   */
  public $depart                      ;
  /**
   * @OA\Property()
   * @var Carbon $retour Return date from the streets.      (when searching queteur, search can be perform by status (about to leave, on the street, returned))
   */
  public $retour                      ;

  /**
   * @OA\Property()
   * @var boolean $active Is the queteur still marked as active
   */
  public $active                      ;
  /**
   * @OA\Property()
   * @var boolean $man Is the queteur a man
   */
  public $man                         ;
  /**
   * @OA\Property()
   * @var Carbon $birthdate Queteur Birthdate. It's used to determine if the queteur is underage or not. Some PointDeQuete a restricted to adults.
   */
  public $birthdate                   ;
  /**
   * @OA\Property()
   * @var boolean $qr_code_printed Is the Queteur QRCode printed or not
   */
  public $qr_code_printed             ;
  /**
   * @OA\Property()
   * @var string $referent_volunteer Who has referred the queteur (non red cross volunteer helping for the fund raising)
   */
  public $referent_volunteer          ;

  /**
   * @OA\Property()
   * @var string $referent_volunteerQueteur Concatenation of first name, last_name and nivol
   */
  public $referent_volunteerQueteur;
  /**
   * @OA\Property()
   * @property  $referent_volunteer_entity
   */
  public $referent_volunteer_entity   ;

  /**
   * @OA\Property(
   *     property="user",
   *          ref="#/components/schemas/UserEntity"
   * )
   * @property UserEntity $user   if the Queteur is also a user of RedCrossQuest, this object is initialised
   */
  public $user;

  /**
   * @OA\Property()
   * @property string $anonymization_token if the queteur data has been anonymised, A GUID is sent to the queteur, so that he can revalue the data the next year and keep it scores.
   */
  public $anonymization_token         ;
  /**
   * @OA\Property()
   * @property Carbon $anonymization_date the date of the anonymization
   */
  public $anonymization_date          ;


  //registration_queteur specific fields
  /**
   * @OA\Property()
   * @property string $ul_registration_token  Token used for registration, when the registration is recorded, the value is taken from ul_settings of the UL the queteur is registering for.
   * When listing registration, it's this value that is used to filter registration for the current unite locale
   */
  public $ul_registration_token       ;
  /**
   * @OA\Property()
   * @property string $queteur_registration_token  it's an UUID generated by the Cloud Function that record the registration from RedQuest. It's used to retrieves the information from the RedQuest app, while waiting for validation.
   */
  public $queteur_registration_token  ;
  /**
   * @OA\Property()
   * @property boolean $registration_approved  has the registration been approved (can be null, true, false)
   */
  public $registration_approved       ;
  /**
   * @OA\Property()
   * @property string $reject_reason    in case of rejection, the reason
   */
  public $reject_reason               ;
  /**
   * @OA\Property()
   * @property int $queteur_id    When this object represent a registration, and a queteur is created or linked, the id of the created/linked queteur
   */
  public $queteur_id                  ;
  /**
   * @OA\Property()
   * @property int $registration_id   When this object represent a registration, the id of the registration
   */
  public $registration_id             ;


  protected $_fieldList = [
    'id','email','first_name','last_name','secteur','nivol','mobile','created','updated',
    'notes','ul_id','ul_name','ul_longitude','ul_latitude','point_quete_id','point_quete_name',
    'depart_theorique','depart','retour','active','man','birthdate','qr_code_printed','referent_volunteer',
    'referent_volunteer_entity','anonymization_token','anonymization_date',
    'ul_registration_token', 'queteur_registration_token', 'registration_approved', 'reject_reason',
    'queteur_id', 'registration_id'
    ];

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger the logger instance
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array &$data, LoggerInterface $logger)
  {
    parent::__construct($logger);

    $this->getInteger('id'                          , $data);
    $this->getEmail  ('email'                       , $data);
    $this->getString ('first_name'                  , $data, 100);
    $this->getString ('last_name'                   , $data, 100);
    $this->getInteger('secteur'                     , $data);
    $this->getString ('nivol'                       , $data, 15);
    $this->getString ('mobile'                      , $data, 20);
    $this->getDate   ('created'                     , $data);
    $this->getDate   ('updated'                     , $data);
    $this->getString ('notes'                       , $data, 500);
    $this->getInteger('ul_id'                       , $data);
    $this->getString ('ul_name'                     , $data, 50);
    $this->getFloat  ('ul_latitude'                 , $data);
    $this->getFloat  ('ul_longitude'                , $data);

    $this->getInteger('point_quete_id'              , $data);
    $this->getString ('point_quete_name'            , $data, 100);
    $this->getDate   ('depart_theorique'            , $data);
    $this->getDate   ('depart'                      , $data);
    $this->getDate   ('retour'                      , $data);

    $this->getBoolean('active'                      , $data);
    $this->getBoolean('man'                         , $data);
    $this->getDate   ('birthdate'                   , $data);

    $this->getBoolean('qr_code_printed'             , $data);
    $this->getInteger('referent_volunteer'          , $data);

    $this->getString ('anonymization_token'         , $data, 36);
    $this->getDate   ('anonymization_date'          , $data);


    $this->getString ('ul_registration_token'       , $data, 36);
    $this->getString ('queteur_registration_token'  , $data, 36);
    $this->getBoolean('registration_approved'       , $data);
    $this->getString ('reject_reason'               , $data, 200);
    $this->getInteger('queteur_id'                  , $data);
    $this->getInteger('registration_id'             , $data);

  }
}
