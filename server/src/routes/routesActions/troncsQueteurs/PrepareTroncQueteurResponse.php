<?php
namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use RedCrossQuest\Entity\TroncInUseEntity;

/**
 * @OA\Schema(schema="PrepareTroncQueteurResponse", required={"troncInUse"})
 */
class PrepareTroncQueteurResponse
{
  /**
   * @OA\Property()
   * @var bool $troncInUse set to true if a tronc_id is already linked to a tronc_queteur row that has retour or depart set to null and not marked as deleted
   */
  public $troncInUse;

  /**
   * @OA\Property()
   * @var TroncInUseEntity[]  $troncInUseInfo An array of TroncInUse objects
   */
  public $troncInUseInfo;

  /**
   * @OA\Property()
   * @var integer $lastInsertId the id of the last inserted TroncQueteur
   */
  public $lastInsertId;


  protected $_fieldList = ['troncInUse', 'troncInUseInfo','lastInsertId'];

  /**
   * @param bool $troncInUse
   * @param TroncInUseEntity[]|null $troncInUseInfo
   */
  public function __construct(bool $troncInUse, $troncInUseInfo=null)
  {
    $this->troncInUse      = $troncInUse;
    $this->troncInUseInfo  = $troncInUseInfo;
  }
}
