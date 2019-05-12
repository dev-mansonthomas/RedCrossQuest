<?php
namespace RedCrossQuest\BusinessService;
use Carbon\Carbon;
use Google\Cloud\Logging\PsrLogger;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;

/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 25/05/2016
 * Time: 10:45
 */





class TroncQueteurBusinessService
{
  protected $logger;
  protected $troncQueteurDBService;
  protected $queteurDBService     ;
  protected $pointQueteDBService  ;
  protected $troncDBService       ;

  public function __construct(\Slim\Container $c)
  {
    $this->logger                = $c->logger;
    $this->troncQueteurDBService = $c->troncQueteurDBService;
    $this->queteurDBService      = $c->queteurDBService;
    $this->pointQueteDBService   = $c->pointQueteDBService;
    $this->troncDBService        = $c->troncDBService;
  }
  
  public function   getLastTroncQueteurFromTroncId(int $tronc_id, int $ulId, int $roleId)
  {
    $troncQueteur               = $this->troncQueteurDBService ->getLastTroncQueteurByTroncId($tronc_id                     , $ulId, $roleId);

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


  public function getTroncQueteurFromTroncQueteurId(int $tronc_queteur_id, int $ulId, int $roleId)
  {
    $troncQueteur               = $this->troncQueteurDBService ->getTroncQueteurById($tronc_queteur_id            , $ulId, $roleId);
    $troncQueteur->queteur      = $this->queteurDBService      ->getQueteurById     ($troncQueteur->queteur_id    , $roleId ==9 ? null: $ulId);
    $troncQueteur->point_quete  = $this->pointQueteDBService   ->getPointQueteById  ($troncQueteur->point_quete_id, $ulId, $roleId);
    $troncQueteur->tronc        = $this->troncDBService        ->getTroncById       ($troncQueteur->tronc_id      , $ulId, $roleId);

    return  $troncQueteur;
  }


  /**
   * In Production : If the current date now() or the date passed in parameter is after the 1st day of the quete, returns true, false otherwise.
   * Other env : it returns always true to be able to test the application
   * @param string $deployment the current deployment value
   * @param Carbon $dateToCheck  preparation date
   * @param $logger PsrLogger logger
   * @return bool true if the quete has already started (or it's not production)
   * @throws \Exception
   */
  public static function hasQueteAlreadyStarted(string $deployment, $dateToCheck=null, $logger)
  {
    if(strlen($deployment) !=1)
    {
      throw new \Exception("\$deployment has an incorrect value ($deployment)");
    }

    if($deployment !== "P")
      return true;
    
    return
      $dateToCheck == null ?
        Carbon::now()->gte(Carbon::createFromFormat("Y-m-d", DailyStatsBeforeRCQDBService::getCurrentQueteStartDate())):
        $dateToCheck ->gte(Carbon::createFromFormat("Y-m-d", DailyStatsBeforeRCQDBService::getCurrentQueteStartDate()));
  }

}
