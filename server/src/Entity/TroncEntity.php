<?php
namespace RedCrossQuest\Entity;
use Carbon\Carbon;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="TroncEntity", required={"type"})
 */

class TroncEntity extends Entity
{
  /**
   * @OA\Property()
   * @var int $id tronc ID
   */
  public int $id      ;

  /**
   * @OA\Property()
   * @var int $ul_id unite local ID
   */
  public int $ul_id   ;

  /**
   * @OA\Property()
   * @var Carbon $created Time of creation of the tronc
   */
  public Carbon $created ;
  /**
   * @OA\Property()
   * @var bool $enabled Tronc is enabled or not
   */
  public bool $enabled ;
  /**
   * @OA\Property()
   * @var string $notes textual notes about the tronc
   */
  public string $notes   ;

  /**
   *
   * @OA\Property()
   * @var int $type Type of tronc {id:1,label:'Tronc'},{id:2,label:'Tronc chez un commerçant'},{id:3,label:'Autre'}
   */
  public int $type    ;
  /**
   * @OA\Property()
   * @var int $nombreTronc Used at creation only, specify the number of tronc to create
   */
  public int $nombreTronc;



  /**
   * GetTroncForDepart, GetTroncForRetour, GetTroncForComptage where we search Tronc that match the state (depart, retour, comptage)
   * @OA\Property()
   * @var int $tronc_queteur_id id of associated TroncQueteur
   */
  public int $tronc_queteur_id ;

  /**
   * GetTroncForDepart, GetTroncForRetour, GetTroncForComptage where we search Tronc that match the state (depart, retour, comptage)
   * @OA\Property()
   * @var string $first_name First Name of the associated Queteur
   */
  public string $first_name;

  /**
   * GetTroncForDepart, GetTroncForRetour, GetTroncForComptage where we search Tronc that match the state (depart, retour, comptage)
   * @OA\Property()
   * @var string $last_name Last Name of the associated Queteur
   */
  public string $last_name;


  /**
   * @OA\Property()
   * @var Carbon $depart The depart date from the troncQueteur
   */
  public Carbon $depart ;

  /**
   * @OA\Property()
   * @var Carbon $retour the Return date from the troncQueteur
   */
  public Carbon $retour ;



  protected array $_fieldList = ['id','ul_id','created','enabled','notes','nombreTronc', 'tronc_queteur_id', 'first_name', 'last_name', 'depart', 'retour'];
  /**
     * Accept an array of data matching properties of this class
     * and create the class
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array &$data, LoggerInterface $logger)
  {
    parent::__construct($logger);
    $this->getInteger('id'                , $data);
    $this->getInteger('ul_id'             , $data);
    $this->getDate   ('created'           , $data);
    $this->getBoolean('enabled'           , $data);
    $this->getString ('notes'             , $data, 500);
    $this->getInteger('type'              , $data);
    $this->getInteger('nombreTronc'       , $data, 0);
    $this->getInteger('tronc_queteur_id'  , $data, 0);
    $this->getString ('first_name'        , $data, 100);
    $this->getString ('last_name'         , $data, 100);
    $this->getDate   ('depart_theorique'  , $data);
    $this->getDate   ('depart'            , $data);
    $this->getDate   ('retour'            , $data);


    }
}
