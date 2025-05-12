<?php
namespace RedCrossQuest\routes\routesActions\queteurs;


use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CountPendingRegistrationResponse", required={"count"})
 */
class CountPendingRegistrationResponse
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
