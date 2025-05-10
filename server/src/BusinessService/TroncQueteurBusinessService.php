<?php
namespace RedCrossQuest\BusinessService;
use Carbon\Carbon;
use Exception;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\TroncQueteurEntity;

/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 25/05/2016
 * Time: 10:45
 */





class TroncQueteurBusinessService
{
  /** @var Logger $logger*/
  protected Logger $logger;
  /** @var TroncQueteurDBService $troncQueteurDBService*/
  protected TroncQueteurDBService $troncQueteurDBService;
  /** @var QueteurDBService $queteurDBService*/
  protected QueteurDBService $queteurDBService     ;
  /** @var PointQueteDBService $pointQueteDBService*/
  protected PointQueteDBService $pointQueteDBService  ;
  /** @var TroncDBService $troncDBService*/
  protected TroncDBService $troncDBService       ;

  /** @var DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService */
  protected DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService;
  
  public function __construct(Logger                  $logger,
                              TroncQueteurDBService   $troncQueteurDBService,
                              QueteurDBService        $queteurDBService,
                              PointQueteDBService     $pointQueteDBService,
                              TroncDBService          $troncDBService,
                              DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService)
  {
    $this->logger                = $logger;
    $this->troncQueteurDBService = $troncQueteurDBService;
    $this->queteurDBService      = $queteurDBService;
    $this->pointQueteDBService   = $pointQueteDBService;
    $this->troncDBService        = $troncDBService;
    $this->dailyStatsBeforeRCQDBService = $dailyStatsBeforeRCQDBService;
  }

  /***
   * @param int $tronc_id
   * @param int $ulId
   * @param int $roleId
   * @return TroncQueteurEntity|null
   * @throws Exception
   */
  public function   getLastTroncQueteurFromTroncId(int $tronc_id, int $ulId, int $roleId):?TroncQueteurEntity
  {
    $troncQueteur = $this->troncQueteurDBService ->getLastTroncQueteurByTroncId($tronc_id, $ulId, $roleId);

    //$this->logger->debug("getLastTroncQueteurFromTroncId - TQ", ["TQ"=>$troncQueteur]);

    if($troncQueteur == null)
      return null;
    
    if($troncQueteur->queteur_id)
    {
      $troncQueteur->queteur      = $this->queteurDBService   ->getQueteurById    ($troncQueteur->queteur_id     , $roleId ==9 ? null: $ulId);
      //$this->logger->debug("getLastTroncQueteurFromTroncId - Queteur", ["Q"=>$troncQueteur->queteur ]);
    }

    if($troncQueteur->point_quete_id)
    {
      $troncQueteur->point_quete  = $this->pointQueteDBService->getPointQueteById ($troncQueteur->point_quete_id , $ulId, $roleId);
      //$this->logger->debug("getLastTroncQueteurFromTroncId - PointQuete", ["PQ"=>$troncQueteur->point_quete]);
    }

    if($troncQueteur->tronc_id)
    {
      $troncQueteur->tronc       = $this->troncDBService      ->getTroncById      ($troncQueteur->tronc_id       , $ulId, $roleId);
      //$this->logger->debug("getLastTroncQueteurFromTroncId - Tronc", ["T"=>$troncQueteur->tronc]);
    }
    return  $troncQueteur;
  }

  /***
   * @param int $tronc_queteur_id
   * @param int $ulId
   * @param int $roleId
   * @return TroncQueteurEntity
   * @throws Exception
   */
  public function getTroncQueteurFromTroncQueteurId(int $tronc_queteur_id, int $ulId, int $roleId):TroncQueteurEntity
  {
    $troncQueteur               = $this->troncQueteurDBService ->getTroncQueteurById($tronc_queteur_id            , $ulId, $roleId);
    //$this->logger->debug("getTroncQueteurFromTroncQueteurId - TQ", ["TQ"=>$troncQueteur]);
    $troncQueteur->queteur      = $this->queteurDBService      ->getQueteurById     ($troncQueteur->queteur_id    , $roleId ==9 ? null: $ulId);
    //$this->logger->debug("getTroncQueteurFromTroncQueteurId - Queteur", ["Q"=>$troncQueteur->queteur ]);
    $troncQueteur->point_quete  = $this->pointQueteDBService   ->getPointQueteById  ($troncQueteur->point_quete_id, $ulId, $roleId);
    //$this->logger->debug("getTroncQueteurFromTroncQueteurId - PointQuete", ["PQ"=>$troncQueteur->point_quete]);
    $troncQueteur->tronc        = $this->troncDBService        ->getTroncById       ($troncQueteur->tronc_id      , $ulId, $roleId);
    //$this->logger->debug("getTroncQueteurFromTroncQueteurId - Tronc", ["T"=>$troncQueteur->tronc]);

    return  $troncQueteur;
  }


  /**
   * In Production : If the current date now() or the date passed in parameter is after the 1st day of the quete, returns true, false otherwise.
   * Other env : it returns always true to be able to test the application
   * @param string $deployment the current deployment value
   * @param Carbon|null $dateToCheck preparation date
   * @return bool true if the quete has already started (or it's not production)
   * @throws Exception
   */
  public function hasQueteAlreadyStarted(string $deployment, ?Carbon $dateToCheck=null):bool
  {
    if(strlen($deployment) !=1)
    {
      throw new Exception("\$deployment has an incorrect value ($deployment)");
    }

    if($deployment !== "P")
      return true;

    /*
    $this->logger->error("checking preparation date",
      [
        "preparationData"=>$dateToCheck,
        "startOfQuete"=> $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate(),
        "resultOfCheckWithNow(null)"=>
        Carbon::now()->gte(Carbon::createFromFormat("Y-m-d H:i:s", $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate()."00:00:00")),
        "resultOfCheckWithSpecificDate"=>
          $dateToCheck ->addHours(2)->gte(Carbon::createFromFormat("Y-m-d H:i:s", $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate()."00:00:00"))
      ]);
    */
    return
      $dateToCheck == null ?
        Carbon::now()->gte(Carbon::createFromFormat("Y-m-d H:i:s", $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate()."00:00:00")):
        $dateToCheck->clone()->addHours(2)->gte(Carbon::createFromFormat("Y-m-d H:i:s", $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate()."00:00:00"));
  }

}
