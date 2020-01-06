<?php
namespace RedCrossQuest\routes\routesActions\queteurs;



class CountPendingRegistrationResponse
{
  /**
   * @var int $count the count of pending registration
   */
  public $count;

  protected $_fieldList = ["count"];

  public function __construct(int $count)
  {
    $this->count       = $count;

  }
}
