<?php
namespace RedCrossQuest\routes\routesActions\spotfire;


use Carbon\Carbon;

/**
 * @OA\Schema(schema="GetSpotfireTokenResponse", required={"validToken"})
 */
class GetSpotfireTokenResponse
{
  /**
   * @OA\Property()
   * @var string $validToken Spotfire valid token
   */
  public $validToken;
  /**
   * @OA\Property()
   * @var Carbon $tokenExpiration Spotfire token expiration date
   */
  public $tokenExpiration;

  protected $_fieldList = ["validToken", "tokenExpiration"];

  public function __construct(string $validToken, Carbon $tokenExpiration)
  {
    $this->validToken      = $validToken;
    $this->tokenExpiration = $tokenExpiration;
  }
}
