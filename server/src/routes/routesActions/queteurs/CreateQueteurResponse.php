<?php
namespace RedCrossQuest\routes\routesActions\queteurs;



class CreateQueteurResponse
{
  /**
   * @var int $queteurId the id of the newly created queteur
   */
  public $queteurId;

  protected $_fieldList = ["queteurId"];

  public function __construct(int $queteurId)
  {
    $this->queteurId       = $queteurId;

  }
}
