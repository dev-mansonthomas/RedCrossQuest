<?php
namespace RedCrossQuest\routes\routesActions\authentication;


use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="SendPasswordInitializationMailResponse", required={"success"})
 */
class SendPasswordInitializationMailResponse
{
  /**
   * @OA\Property()
   * @var ?bool $success the action did succeed or not
   */
  public ?bool $success;

  /**
   * @OA\Property()
   * @var string|null $email The email of the reset password
   */
  public ?string $email;

  protected array $_fieldList = ['success', 'email'];

  public function __construct(bool $success, ?string $email=null)
  {
    $this->success  = $success;
    $this->email    = $email;
  }
}
