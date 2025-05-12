<?php
namespace RedCrossQuest\routes\routesActions\queteurs;


use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CountInactiveQueteursResponse", required={"count"})
 */
class CountInactiveQueteursResponse
{
  /**
   * @OA\Property()
   * @var int|null $count the count of pending registration
   */
  public ?int $count;

  protected array $_fieldList = ["count"];

  public function __construct(int $count)
  {
    $this->count       = $count;

  }
}
