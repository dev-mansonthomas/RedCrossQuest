<?php
namespace RedCrossQuest\routes\routesActions\settings;


use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\Entity\UniteLocaleSettingsEntity;
use RedCrossQuest\Entity\UserEntity;

/**
 * @OA\Schema(schema="GetAllULSettingsResponse", required={"mapKey", "RGPDVideo", "RedQuestDomain","RCQVersion", "FirstDay","ul", "ul_settings", "user"})
 */
class GetAllULSettingsResponse
{
  /**
   * @OA\Property()
   * @var string $mapKey Google Maps API Key
   */
  public $mapKey;

  /**
   * @OA\Property()
   * @var string $RGPDVideo Link to the RGPD Vidéo
   */
  public $RGPDVideo;

  /**
   * @OA\Property()
   * @var string $RedQuestDomain Domain name of the RedQuest application
   */
  public $RedQuestDomain;

  /**
   * @OA\Property()
   * @var string $RCQVersion Version of RCQ Backend
   */
  public $RCQVersion;

  /**
   * @OA\Property()
   * @var string $FirstDay date of the first day of the fundraising of this year. Format YYYY-MM-DD.
   */
  public $FirstDay;

  /**
   * @OA\Property()
   * @var UniteLocaleEntity $ul The details of user's UL
   */
  public $ul;

  /**
   * @OA\Property()
   * @var UniteLocaleSettingsEntity $ul_settings Settings of user's UL
   */
  public $ul_settings;

  /**
   * @OA\Property()
   * @var UserEntity $user Info about the current User
   */
  public $user;

  protected $_fieldList = ["mapKey", "RGPDVideo", "RedQuestDomain","RCQVersion", "FirstDay","ul", "ul_settings", "user"];

  public function __construct(string $mapKey, string $RGPDVideo, string $RedQuestDomain, string $RCQVersion, string $FirstDay)
  {
    $this->mapKey         = $mapKey;
    $this->RGPDVideo      = $RGPDVideo;
    $this->RedQuestDomain = $RedQuestDomain;
    $this->RCQVersion     = $RCQVersion;
    $this->FirstDay       = $FirstDay;
  }
}
