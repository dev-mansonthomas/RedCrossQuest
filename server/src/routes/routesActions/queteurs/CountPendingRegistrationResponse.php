<?php
namespace RedCrossQuest\routes\routesActions\queteurs;


/**
 * @OA\Schema(schema="CountPendingRegistrationResponse", required={"count"})
 */
class CountPendingRegistrationResponse
{
  /**
   * @OA\Property()
   * @var int $count the count of pending registration
   */
  public $count;

  protected $_fieldList = ["count"];

  public function __construct(int $count)
  {
    $this->count       = $count;

  }
}
