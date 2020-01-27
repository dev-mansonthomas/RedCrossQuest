<?php
namespace RedCrossQuest\routes\routesActions\settings;


use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\Entity\UniteLocaleSettingsEntity;
use RedCrossQuest\Entity\UserEntity;

/**
 * @OA\Schema(schema="GetULSettingsResponse", required={"mapKey", "RGPDVideo", "RedQuestDomain","RCQVersion", "FirstDay","ul", "ul_settings", "user"})
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
