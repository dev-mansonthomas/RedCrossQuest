<?php
namespace RedCrossQuest\routes\routesActions\authentication;

/**
 * @OA\Schema(schema="AuthenticationResponse", required={"token"})
 */

class AuthenticationResponse
{
  /**
   * @OA\Property()
   * @var string token  JWT
   */
  public $token;

  protected $_fieldList = ['token'];


  public function __construct(string $token)
  {
    $this->token = $token;
  }
}
