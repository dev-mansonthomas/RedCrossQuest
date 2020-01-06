<?php
namespace RedCrossQuest\routes\routesActions\queteurs;



class ApproveQueteurRegistrationResponse
{
  /**
   * @var int $queteurId the id of the newly created pointQuete
   */
  public $queteurId;

  protected $_fieldList = ["queteurId"];

  public function __construct(int $queteurId)
  {
    $this->queteurId       = $queteurId;

  }
}
