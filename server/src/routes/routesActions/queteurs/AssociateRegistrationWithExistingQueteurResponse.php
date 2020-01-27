<?php
namespace RedCrossQuest\routes\routesActions\queteurs;


/**
 * @OA\Schema(schema="AssociateRegistrationWithExistingQueteurResponse", required={"queteurId"})
 */
class AssociateRegistrationWithExistingQueteurResponse
{
  /**
   * @OA\Property()
   * @var int $queteurId the id of the newly created pointQuete
   */
  public $queteurId;

  protected $_fieldList = ["queteurId"];

  public function __construct(int $queteurId)
  {
    $this->queteurId       = $queteurId;

  }
}
