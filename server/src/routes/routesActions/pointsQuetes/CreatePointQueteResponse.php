<?php
namespace RedCrossQuest\routes\routesActions\pointsQuetes;



class CreatePointQueteResponse
{
  /**
   * @var int $pointQueteId the id of the newly created pointQuete
   */
  public $pointQueteId;

  protected $_fieldList = ["pointQueteId"];

  public function __construct(int $pointQueteId)
  {
    $this->pointQueteId       = $pointQueteId;

  }
}
