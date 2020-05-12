<?php
namespace RedCrossQuest\routes\routesActions\troncsQueteurs;



class PrepareTroncQueteurQueteNotStartedResponse
{
  /**
   * @var bool $queteHasNotStartedYet the id of the newly created queteur
   */
  public $queteHasNotStartedYet = true;

  protected $_fieldList = ["queteHasNotStartedYet"];

  public function __construct()
  {
  }
}
