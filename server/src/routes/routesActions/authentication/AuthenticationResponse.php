<?php
namespace RedCrossQuest\routes\routesActions\authentication;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="AuthenticationResponse", required={"token"})
 */

class AuthenticationResponse
{
  /**
   * @OA\Property()
   * @var ?string token  JWT
   */
  public ?string $token;

  protected array $_fieldList = ['token'];


  public function __construct(string $token)
  {
    $this->token = $token;
  }
}
