<?php
namespace RedCrossQuest\routes\routesActions\queteurs;


/**
 * @OA\Schema(schema="CreateQueteurResponse", required={"queteurId"})
 */
class CreateQueteurResponse
{
  /**
   * @OA\Property()
   * @var int $queteurId the id of the newly created queteur
   */
  public $queteurId;

  protected $_fieldList = ["queteurId"];

  public function __construct(int $queteurId)
  {
    $this->queteurId       = $queteurId;

  }
}
