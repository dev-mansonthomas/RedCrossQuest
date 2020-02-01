<?php
namespace RedCrossQuest\routes\routesActions\settings;


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
  public $ul_settings;


  protected $_fieldList = ["ul_settings"];

  public function __construct()
  {
  }
}
