<?php
namespace RedCrossQuest\BusinessService;
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
      $troncQueteur->queteur      = $this->queteurDBService      ->getQueteurById              ($troncQueteur->queteur_id     , $ulId, $roleId);
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
    $troncQueteur->queteur      = $this->queteurDBService      ->getQueteurById     ($troncQueteur->queteur_id    , $ulId, $roleId);
    $troncQueteur->point_quete  = $this->pointQueteDBService   ->getPointQueteById  ($troncQueteur->point_quete_id, $ulId, $roleId);
    $troncQueteur->tronc        = $this->troncDBService        ->getTroncById       ($troncQueteur->tronc_id      , $ulId, $roleId);

    return  $troncQueteur;
  }

}
