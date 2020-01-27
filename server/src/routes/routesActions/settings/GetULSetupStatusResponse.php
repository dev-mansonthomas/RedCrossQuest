<?php
namespace RedCrossQuest\routes\routesActions\settings;

/**
 * @OA\Schema(schema="GetULSetupStatusResponse", required={"mapKey", "RGPDVideo", "RedQuestDomain","RCQVersion", "FirstDay","ul", "ul_settings", "user"})
 */
class GetULSetupStatusResponse
{
  /**
   * @OA\Property()
   * @var integer $numberOfQueteur Number of Queteur currently in DB
   */
  public $numberOfQueteur;
  /**
   * @OA\Property()
   * @var integer $numberOfUser Number of User currently in DB
   */
  public $numberOfUser;
  /**
   * @OA\Property()
   * @var integer $numberOfPointQuete Number of PointQuete currently in DB
   */
  public $numberOfPointQuete;
  /**
   * @OA\Property()
   * @var integer $numberOfDailyStats Number of DailyStats currently in DB
   */
  public $numberOfDailyStats;
  /**
   * @OA\Property()
   * @var integer $numberOfTroncs Number of Troncs currently in DB
   */
  public $numberOfTroncs;

  /**
   * @OA\Property()
   * @var boolean $queteurIncomplete Is setup of Queteur considered as completed
   */
  public $queteurIncomplete;
  /**
   * @OA\Property()
   * @var boolean $userIncomplete Is setup of User considered as completed
   */
  public $userIncomplete;
  /**
   * @OA\Property()
   * @var boolean $pointQueteIncomplete Is setup of PointQuete considered as completed
   */
  public $pointQueteIncomplete;
  /**
   * @OA\Property()
   * @var boolean $dailyStatsIncomplete Is setup of dailyStats considered as completed
   */
  public $dailyStatsIncomplete;
  /**
   * @OA\Property()
   * @var boolean $troncsIncomplete Is setup of Troncs considered as completed
   */
  public $troncsIncomplete;
  /**
   * @OA\Property()
   * @var boolean $BasePointQueteCreated Has the Base being automatically created as a 'Point de Quete'
   */
  public $BasePointQueteCreated;
  
  protected $_fieldList = ["numberOfQueteur", "numberOfUser", "numberOfPointQuete", "numberOfDailyStats", "numberOfTroncs", "queteurIncomplete", "userIncomplete", "pointQueteIncomplete", "dailyStatsIncomplete", "troncsIncomplet", "BasePointQueteCreated"];

  public function __construct()
  {
  }
}
