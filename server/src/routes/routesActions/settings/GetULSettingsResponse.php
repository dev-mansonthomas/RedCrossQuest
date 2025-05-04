<?php
namespace RedCrossQuest\routes\routesActions\settings;


use OpenApi\Annotations as OA;
use RedCrossQuest\Entity\UniteLocaleSettingsEntity;

/**
 * @OA\Schema(schema="GetULSettingsResponse", required={"ul_settings"})
 */
class GetULSettingsResponse
{
  /**
   * @OA\Property()
   * @var UniteLocaleSettingsEntity $ul_settings Settings of user's UL
   */
  public UniteLocaleSettingsEntity $ul_settings;


  protected array $_fieldList = ["ul_settings"];

  public function __construct()
  {
  }
}
