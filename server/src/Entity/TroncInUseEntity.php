<?php
namespace RedCrossQuest\Entity;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="TroncInUseEntity", required={"id", "depart_theorique","queteur_id","tronc_id","first_name","last_name","email","mobile","nivol","status"})
 */
class TroncInUseEntity  extends Entity
{
  /**
   * @OA\Property()
   * @var int $id TroncQueteur ID
   */
  public $id;
  public $depart_theorique;
  public $depart;
  /**
   * @OA\Property()
   * @var int $queteur_id queteur ID
   */
  public $queteur_id;
  /**
   * @OA\Property()
   * @var int $tronc_id Tronc ID
   */
  public $tronc_id;
  /**
   * @OA\Property()
   * @var string $first_name Queteur First Name
   */
  public $first_name;
  /**
   * @OA\Property()
   * @var string $last_name Queteur Last Name
   */
  public $last_name;
  /**
   * @OA\Property()
   * @var string $email Queteur email
   */
  public $email;
  /**
   * @OA\Property()
   * @var string $mobile Queteur mobile
   */
  public $mobile;
  /**
   * @OA\Property()
   * @var string $nivol Queteur Nivol
   */
  public $nivol;
  /**
   * @OA\Property()
   * @var string $status Status of the check : TRONC_IN_USE (other people have this tronc assigned (and depart or retour is null, deleted=false)) or QUETEUR_HAS_ALREADY_HAS_A_TRONC (a tronc_queteur row exist and have the queteur_id)
   */
  public $status;

  protected $_fieldList = ['id','depart_theorique','depart','queteur_id','tronc_id','first_name','last_name','email','mobile','nivol', 'status'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, LoggerInterface $logger)
  {
    parent::__construct($logger);
    $this->getInteger ('id'               , $data);
    $this->getDate    ('depart_theorique' , $data);
    $this->getDate    ('depart'           , $data);
    $this->getInteger ('queteur_id'       , $data);
    $this->getInteger ('tronc_id'         , $data);
    $this->getString  ('first_name'       , $data, 100);
    $this->getString  ('last_name'        , $data, 100);
    $this->getString  ('email'            , $data, 64);
    $this->getString  ('mobile'           , $data, 20);
    $this->getString  ('nivol'            , $data, 15);
    $this->getString  ('status'           , $data, 50);
  }
}
