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
  public $id      ;

  /**
   * @OA\Property()
   * @var int $ul_id unite local ID
   */
  public $ul_id   ;

  /**
   * @OA\Property()
   * @var Carbon $created Time of creation of the tronc
   */
  public $created ;
  /**
   * @OA\Property()
   * @var boolean $enabled Tronc is enabled or not
   */
  public $enabled ;
  /**
   * @OA\Property()
   * @var string $notes textual notes about the tronc
   */
  public $notes   ;

  /**
   *
   * @OA\Property()
   * @var integer $type Type of tronc {id:1,label:'Tronc'},{id:2,label:'Tronc chez un commerçant'},{id:3,label:'Autre'}
   */
  public $type    ;
  /**
   * @OA\Property()
   * @var integer $nombreTronc Used at creation only, specify the number of tronc to create
   */
  public $nombreTronc;

  protected $_fieldList = ['id','ul_id','created','enabled','notes','nombreTronc'];
  /**
     * Accept an array of data matching properties of this class
     * and create the class
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, LoggerInterface $logger)
  {
    parent::__construct($logger);
      $this->getInteger('id'          , $data);
      $this->getInteger('ul_id'       , $data);
      $this->getDate   ('created'     , $data);
      $this->getBoolean('enabled'     , $data);
      $this->getString ('notes'       , $data, 500);
      $this->getInteger('type'        , $data);
      $this->getInteger('nombreTronc' , $data);


    }
}
