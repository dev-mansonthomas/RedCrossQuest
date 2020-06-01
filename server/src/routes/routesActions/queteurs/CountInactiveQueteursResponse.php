<?php
namespace RedCrossQuest\routes\routesActions\queteurs;


/**
 * @OA\Schema(schema="CountInactiveQueteursResponse", required={"count"})
 */
class CountInactiveQueteursResponse
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
