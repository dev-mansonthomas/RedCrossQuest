<?php
namespace RedCrossQuest\routes\routesActions\pointsQuetes;


use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CreatePointQueteResponse", required={"id"})
 */
class CreatePointQueteResponse
{
  /**
   * @OA\Property()
   * @var ?int $id the id of the newly created pointQuete
   */
  public ?int $id;

  protected array $_fieldList = ["id"];

  public function __construct(int $pointQueteId)
  {
    $this->id       = $pointQueteId;

  }
}
