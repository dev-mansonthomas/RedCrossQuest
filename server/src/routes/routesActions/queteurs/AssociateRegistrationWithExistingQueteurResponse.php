<?php
namespace RedCrossQuest\routes\routesActions\queteurs;


use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="AssociateRegistrationWithExistingQueteurResponse", required={"queteurId"})
 */
class AssociateRegistrationWithExistingQueteurResponse
{
  /**
   * @OA\Property()
   * @var ?int $queteurId the id of the newly created pointQuete
   */
  public ?int $queteurId;

  protected array $_fieldList = ["queteurId"];

  public function __construct(int $queteurId)
  {
    $this->queteurId       = $queteurId;

  }
}
