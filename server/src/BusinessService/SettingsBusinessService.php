<?php

namespace RedCrossQuest\BusinessService;


use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\DBService\UserDBService;

class SettingsBusinessService
{
  protected $logger;
  /** @var QueteurDBService */
  protected $queteurDBService;
  /** @var PointQueteDBService */
  protected $pointQueteDBService;
  /** @var UserDBService */
  protected $userDBService;
  /** @var DailyStatsBeforeRCQDBService */
  protected $dailyStatsBeforeRCQDBService;
  /** @var TroncDBService */
  protected $troncDBService;

  public function __construct(\Slim\Container $c)
  {
    $this->logger                       = $c->logger                      ;
    $this->queteurDBService             = $c->queteurDBService            ;
    $this->userDBService                = $c->userDBService               ;
    $this->pointQueteDBService          = $c->pointQueteDBService         ;
    $this->dailyStatsBeforeRCQDBService = $c->dailyStatsBeforeRCQDBService;
    $this->troncDBService               = $c->troncDBService              ;
  }


  /**
   * Fetch data about the setup :
   *  Number of queteurs
   *  Number of users
   *  Number of PointQuete
   *  => if 0, it creates the Base one with the data contained in UL table
   * @param integer $ulId  The ID of the Unité Locale
   * @return array an associative array with the informations.
   * @throws \Exception   if something wrong happen
   */
  public function getSetupStatus(int $ulId)
  {

    $setupStatus = [];

    $setupStatus["numberOfQueteur"   ] = $this->queteurDBService            ->getNumberOfQueteur    ($ulId);
    $setupStatus["numberOfUser"      ] = $this->userDBService               ->getNumberOfUser       ($ulId);
    $setupStatus["numberOfPointQuete"] = $this->pointQueteDBService         ->getNumberOfPointQuete ($ulId);
    $setupStatus["numberOfDailyStats"] = $this->dailyStatsBeforeRCQDBService->getNumberOfDailyStats ($ulId);
    $setupStatus["numberOfTroncs"    ] = $this->troncDBService              ->getNumberOfTroncs     ($ulId);

    if($setupStatus["numberOfQueteur"   ] <=10)
    {
      $setupStatus["queteurIncomplete"   ]=true;
    }
    if($setupStatus["numberOfUser"   ] == 1)
    {
      $setupStatus["userIncomplete"   ]=true;
    }
    if($setupStatus["numberOfPointQuete"   ] <=10)
    {
      $setupStatus["pointQueteIncomplete"   ]=true;
    }
    if($setupStatus["numberOfDailyStats"   ] <=17)
    {
      $setupStatus["dailyStatsIncomplete"   ]=true;
    }

    if($setupStatus["numberOfTroncs"   ] <=5)
    {
      $setupStatus["troncsIncomplete"   ]=true;
    }

    if($setupStatus["numberOfPointQuete"] == 0)
    {
      $this->pointQueteDBService->initBasePointQuete($ulId);
      $setupStatus["BasePointQueteCreated"] = 1;
    }

  return $setupStatus;
  }

}
