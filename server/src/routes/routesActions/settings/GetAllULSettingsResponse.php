<?php
namespace RedCrossQuest\routes\routesActions\settings;


use OpenApi\Annotations as OA;
use RedCrossQuest\Entity\ULPreferencesEntity;
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
   * @var string|null $mapKey Google Maps API Key
   */
  public ?string $mapKey;

  /**
   * @OA\Property()
   * @var string|null $RGPDVideo Link to the RGPD Vidéo
   */
  public ?string $RGPDVideo;

  /**
   * @OA\Property()
   * @var string|null $RedQuestDomain Domain name of the RedQuest application
   */
  public ?string $RedQuestDomain;

  /**
   * @OA\Property()
   * @var string|null $RCQVersion Version of RCQ Backend
   */
  public ?string $RCQVersion;

  /**
   * @OA\Property()
   * @var string|null $FirstDay date of the first day of the fundraising of this year. Format YYYY-MM-DD.
   */
  public ?string $FirstDay;

  /**
   * @OA\Property()
   * @var UniteLocaleEntity|null $ul The details of user's UL
   */
  public ?UniteLocaleEntity $ul;

  /**
   * @OA\Property()
   * @var ULPreferencesEntity|null $ul_settings Settings of user's UL
   */
  public ?ULPreferencesEntity $ul_settings;

  /**
   * @OA\Property()
   * @var UserEntity|null $user Info about the current User
   */
  public ?UserEntity $user;

  protected array $_fieldList = ["mapKey", "RGPDVideo", "RedQuestDomain","RCQVersion", "FirstDay","ul", "ul_settings", "user"];

  public function __construct(string $mapKey, string $RGPDVideo, string $RedQuestDomain, string $RCQVersion, string $FirstDay)
  {
    $this->mapKey         = $mapKey;
    $this->RGPDVideo      = $RGPDVideo;
    $this->RedQuestDomain = $RedQuestDomain;
    $this->RCQVersion     = $RCQVersion;
    $this->FirstDay       = $FirstDay;
  }
}
