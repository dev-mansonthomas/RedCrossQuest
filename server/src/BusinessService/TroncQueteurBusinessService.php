<?php
namespace RedCrossQuest\BusinessService;
use Carbon\Carbon;
use Exception;
use Psr\Log\LoggerInterface;
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
  /** @var LoggerInterface $logger*/
  protected $logger;
  /** @var TroncQueteurDBService $troncQueteurDBService*/
  protected $troncQueteurDBService;
  /** @var QueteurDBService $queteurDBService*/
  protected $queteurDBService     ;
  /** @var PointQueteDBService $pointQueteDBService*/
  protected $pointQueteDBService  ;
  /** @var TroncDBService $troncDBService*/
  protected $troncDBService       ;

  /** @var DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService */
  protected $dailyStatsBeforeRCQDBService;
  
  public function __construct(LoggerInterface         $logger,
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
   * @return TroncQueteurEntity
   * @throws Exception
   */
  public function   getLastTroncQueteurFromTroncId(int $tronc_id, int $ulId, int $roleId):TroncQueteurEntity
  {
    $troncQueteur               = $this->troncQueteurDBService ->getLastTroncQueteurByTroncId($tronc_id                     , $ulId);

    //if no tronc_queteur is found, a troncQueteur is still return with "rowCount"=0, and the tronc_id
    if($troncQueteur->queteur_id)
    {
      $troncQueteur->queteur      = $this->queteurDBService      ->getQueteurById              ($troncQueteur->queteur_id     , $roleId ==9 ? null: $ulId);
    }

    if($troncQueteur->point_quete_id)
    {
      $troncQueteur->point_quete  = $this->pointQueteDBService   ->getPointQueteById           ($troncQueteur->point_quete_id , $ulId, $roleId);
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
    $troncQueteur               = $this->troncQueteurDBService ->getTroncQueteurById($tronc_queteur_id            , $ulId);
    $troncQueteur->queteur      = $this->queteurDBService      ->getQueteurById     ($troncQueteur->queteur_id    , $roleId ==9 ? null: $ulId);
    $troncQueteur->point_quete  = $this->pointQueteDBService   ->getPointQueteById  ($troncQueteur->point_quete_id, $ulId, $roleId);
    $troncQueteur->tronc        = $this->troncDBService        ->getTroncById       ($troncQueteur->tronc_id      , $ulId);

    return  $troncQueteur;
  }


  /**
   * In Production : If the current date now() or the date passed in parameter is after the 1st day of the quete, returns true, false otherwise.
   * Other env : it returns always true to be able to test the application
   * @param string $deployment the current deployment value
   * @param Carbon $dateToCheck  preparation date
   * @return bool true if the quete has already started (or it's not production)
   * @throws Exception
   */
  public function hasQueteAlreadyStarted(string $deployment, $dateToCheck=null):bool
  {
    if(strlen($deployment) !=1)
    {
      throw new Exception("\$deployment has an incorrect value ($deployment)");
    }

    if($deployment !== "P")
      return true;

    /*
    $logger->debug("checking preparation date",["preparationData"=>$dateToCheck, "startOfQuete"=> DailyStatsBeforeRCQDBService::getCurrentQueteStartDate(),
      "resultOfCheck"=>$dateToCheck ->gte(Carbon::createFromFormat("Y-m-d", DailyStatsBeforeRCQDBService::getCurrentQueteStartDate()))]);
      */
    return
      $dateToCheck == null ?
        Carbon::now()->gte(Carbon::createFromFormat("Y-m-d H:i:s", $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate()."00:00:00")):
        $dateToCheck ->gte(Carbon::createFromFormat("Y-m-d H:i:s", $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate()."00:00:00"));
  }

}
