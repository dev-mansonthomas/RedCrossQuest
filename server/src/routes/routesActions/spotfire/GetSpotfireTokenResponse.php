<?php
namespace RedCrossQuest\routes\routesActions\spotfire;


use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\Entity\UniteLocaleSettingsEntity;
use RedCrossQuest\Entity\UserEntity;

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

  protected $_fieldList = ["validToken"];

  public function __construct(string $validToken)
  {
    $this->validToken     = $validToken;
  }
}
