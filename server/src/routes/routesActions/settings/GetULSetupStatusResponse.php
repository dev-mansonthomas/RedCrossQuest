<?php
namespace RedCrossQuest\routes\routesActions\settings;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetULSetupStatusResponse", required={"mapKey", "RGPDVideo", "RedQuestDomain","RCQVersion", "FirstDay","ul", "ul_settings", "user"})
 */
class GetULSetupStatusResponse
{
  /**
   * @OA\Property()
   * @var integer|null $numberOfQueteur Number of Queteur currently in DB
   */
  public ?int $numberOfQueteur;
  /**
   * @OA\Property()
   * @var integer|null $numberOfUser Number of User currently in DB
   */
  public ?int $numberOfUser;
  /**
   * @OA\Property()
   * @var integer|null $numberOfPointQuete Number of PointQuete currently in DB
   */
  public ?int $numberOfPointQuete;
  /**
   * @OA\Property()
   * @var integer|null $numberOfDailyStats Number of DailyStats currently in DB
   */
  public ?int $numberOfDailyStats;
  /**
   * @OA\Property()
   * @var integer|null $numberOfTroncs Number of Troncs currently in DB
   */
  public ?int $numberOfTroncs;

  /**
   * @OA\Property()
   * @var boolean|null $queteurIncomplete Is setup of Queteur considered as completed
   */
  public ?bool $queteurIncomplete;
  /**
   * @OA\Property()
   * @var boolean|null $userIncomplete Is setup of User considered as completed
   */
  public ?bool $userIncomplete;
  /**
   * @OA\Property()
   * @var boolean|null $pointQueteIncomplete Is setup of PointQuete considered as completed
   */
  public ?bool $pointQueteIncomplete;
  /**
   * @OA\Property()
   * @var boolean|null $dailyStatsIncomplete Is setup of dailyStats considered as completed
   */
  public ?bool $dailyStatsIncomplete;
  /**
   * @OA\Property()
   * @var boolean|null $troncsIncomplete Is setup of Troncs considered as completed
   */
  public ?bool $troncsIncomplete;
  /**
   * @OA\Property()
   * @var boolean|null $BasePointQueteCreated Has the Base being automatically created as a 'Point de Quete'
   */
  public ?bool $BasePointQueteCreated;
  
  protected array $_fieldList = ["numberOfQueteur", "numberOfUser", "numberOfPointQuete", "numberOfDailyStats", "numberOfTroncs", "queteurIncomplete", "userIncomplete", "pointQueteIncomplete", "dailyStatsIncomplete", "troncsIncomplet", "BasePointQueteCreated"];

  public function __construct()
  {
  }
}
