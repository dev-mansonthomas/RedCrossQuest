<?php
namespace RedCrossQuest\Entity;
use Carbon\Carbon;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="PointQueteEntity", required={"name","latitude","longitude","address","postal_code","city","minor_allowed","enabled","type"})
 */
class PointQueteEntity  extends Entity
{
  /**
   * @OA\Property()
   * @var int $id pointQuete Id
   */
  public $id                ;
  /**
   * @OA\Property()
   * @var int $ul_id UniteLocal ID
   */
  public $ul_id             ;
  /**
   * @OA\Property()
   * @var string $code Code for the point de Quete: short string
   */
  public $code              ;
  /**
   * @OA\Property()
   * @var string $name Name identifying the point de Quete
   */
  public $name              ;
  /**
   * @OA\Property()
   * @var float $latitude GPS latitude (get from google maps API)
   */
  public $latitude          ;
  /**
   * @OA\Property()
   * @var float $id GPS longitude (get from google maps API)
   */
  public $longitude         ;
  /**
   * @OA\Property()
   * @var string $address Street number and name of the address
   */
  public $address           ;
  /**
   * @OA\Property()
   * @var string $postal_code Postal code part of the address
   */
  public $postal_code       ;
  /**
   * @OA\Property()
   * @var string $city City part of the address
   */
  public $city              ;
  /**
   * @OA\Property()
   * @var int $max_people Maximum number of people advised to go on PointDeQuete.(no control are done on this, it's advisory)
   */
  public $max_people        ;
  /**
   * @OA\Property()
   * @var string $advice Advise on the PointDeQuete
   */
  public $advice            ;
  /**
   * @OA\Property()
   * @var string $localization notes on how to reach the PointDeQuete location
   */
  public $localization      ;
  /**
   * @OA\Property()
   * @var boolean $minor_allowed are underage
   */
  public $minor_allowed     ;
  /**
   * @OA\Property()
   * @var Carbon $created when was the pointDeQuete created
   */
  public $created           ;
  /**
   * @OA\Property()
   * @var boolean $enabled Is the pointDeQuete enabled or not
   */
  public $enabled           ;
  /**
   * @OA\Property()
   * @var int $type id of the type of PointQuete        {id:1,label:'Voie Publique / Feux Rouge'},{id:2,label:'Piéton'},{id:3,label:'Commerçant'},{id:4,label:'Base UL'},{id:5,label:'Autre'}
   */
  public $type              ;
  /**
   * @OA\Property()
   * @var int $time_to_reach number of minutes required to reached the pointDeQuete
   */
  public $time_to_reach     ;
  /**
   * @OA\Property()
   * @var int $transport_to_reach id of the transport type  {id:1,label:'A Pied'},{id:2,label:'Voiture'}{id:3,label:'Vélo'},{id:4,label:'Train/Tram'},{id:5,label:'Autre'}
   */
  public $transport_to_reach;

  protected array $_fieldList = ['id','ul_id','code','name','latitude','longitude','address','postal_code','city','max_people','advice','localization','minor_allowed','created','enabled','type','time_to_reach','transport_to_reach'];

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

    $this->getInteger('id'           , $data);
    $this->getInteger('ul_id'        , $data);
    $this->getString ('code'         , $data, 10);
    $this->getString ('name'         , $data, 100);
    $this->getFloat  ('latitude'     , $data);
    $this->getFloat  ('longitude'    , $data);
    $this->getString ('address'      , $data, 70);
    $this->getInteger('postal_code'  , $data);
    $this->getString ('city'         , $data, 70);
    $this->getInteger('max_people'   , $data, 50);
    $this->getString ('advice'       , $data, 500);
    $this->getString ('localization' , $data, 500);
    $this->getBoolean('minor_allowed', $data);
    $this->getDate   ('created'      , $data);
    $this->getBoolean('enabled'      , $data);


    $this->getInteger( 'type'               , $data);
    $this->getInteger( 'time_to_reach'      , $data);
    $this->getInteger( 'transport_to_reach' , $data);

  }
}
