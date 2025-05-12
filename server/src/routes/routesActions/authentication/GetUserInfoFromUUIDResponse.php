<?php
namespace RedCrossQuest\routes\routesActions\authentication;


use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetUserInfoFromUUIDResponse", required={"success"})
 */

class GetUserInfoFromUUIDResponse
{
  /**
   * @OA\Property()
   * @var ?bool $success the action did succeed or not
   */
  public ?bool $success;

  /**
   * @OA\Property()
   * @var ?string  $nivol The nivol corresponding to the UUID
   */
  public ?string $nivol;

  protected array $_fieldList = ['success', 'nivol'];

  public function __construct(bool $success, ?string $nivol=null)
  {
    $this->success  = $success;
    $this->nivol    = $nivol;
  }
}
