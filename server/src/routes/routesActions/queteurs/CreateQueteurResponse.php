<?php
namespace RedCrossQuest\routes\routesActions\queteurs;


use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CreateQueteurResponse", required={"queteurId"})
 */
class CreateQueteurResponse
{
  /**
   * @OA\Property()
   * @var int $queteurId the id of the newly created queteur
   */
  public int $queteurId;

  protected array $_fieldList = ["queteurId"];

  public function __construct(int $queteurId)
  {
    $this->queteurId       = $queteurId;

  }
}
