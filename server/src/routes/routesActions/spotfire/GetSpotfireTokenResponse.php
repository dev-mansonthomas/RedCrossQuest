<?php
namespace RedCrossQuest\routes\routesActions\spotfire;


use Carbon\Carbon;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetSpotfireTokenResponse", required={"validToken"})
 */
class GetSpotfireTokenResponse
{
  /**
   * @OA\Property()
   * @var string $validToken Spotfire valid token
   */
  public string $validToken;
  /**
   * @OA\Property()
   * @var Carbon $tokenExpiration Spotfire token expiration date
   */
  public Carbon $tokenExpiration;

  protected array $_fieldList = ["validToken", "tokenExpiration"];

  public function __construct(string $validToken, Carbon $tokenExpiration)
  {
    $this->validToken      = $validToken;
    $this->tokenExpiration = $tokenExpiration;
  }
}
