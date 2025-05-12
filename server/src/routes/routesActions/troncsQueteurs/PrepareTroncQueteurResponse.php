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
   * @var bool|null $troncInUse set to true if a tronc_id is already linked to a tronc_queteur row that has retour or depart set to null and not marked as deleted
   */
  public bool $troncInUse;

  /**
   * @OA\Property()
   * @var TroncInUseEntity[]|null  $troncInUseInfo An array of TroncInUse objects
   */
  public ?array $troncInUseInfo;

  /**
   * @OA\Property()
   * @var integer|null $lastInsertId the id of the last inserted TroncQueteur
   */
  public ?int $lastInsertId;


  protected $_fieldList = ['troncInUse', 'troncInUseInfo','lastInsertId'];

  /**
   * @param bool $troncInUse
   * @param TroncInUseEntity[]|null $troncInUseInfo
   */
  public function __construct(bool $troncInUse, ?array $troncInUseInfo=null)
  {
    $this->troncInUse      = $troncInUse;
    $this->troncInUseInfo  = $troncInUseInfo;
  }
}
