<?php
namespace RedCrossQuest\routes\routesActions\pointsQuetes;


/**
 * @OA\Schema(schema="CreatePointQueteResponse", required={"id"})
 */
class CreatePointQueteResponse
{
  /**
   * @OA\Property()
   * @var int $id the id of the newly created pointQuete
   */
  public $id;

  protected $_fieldList = ["id"];

  public function __construct(int $pointQueteId)
  {
    $this->id       = $pointQueteId;

  }
}
