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

  public function __construct($logger,
                              $troncQueteurDBService ,
                              $queteurDBService      ,
                              $pointQueteDBService   ,
                              $troncDBService        )
  {
    $this->logger = $logger;

    $this->troncQueteurDBService = $troncQueteurDBService;
    $this->queteurDBService      = $queteurDBService;
    $this->pointQueteDBService   = $pointQueteDBService;
    $this->troncDBService        = $troncDBService;
  }
  
  public function getLastTroncQueteurFromTroncId(int $tronc_id, int $ulId)
  {
    $troncQueteur               = $this->troncQueteurDBService ->getLastTroncQueteurByTroncId($tronc_id                     , $ulId );
    $troncQueteur->queteur      = $this->queteurDBService      ->getQueteurById              ($troncQueteur->queteur_id     , $ulId );
    $troncQueteur->point_quete  = $this->pointQueteDBService   ->getPointQueteById           ($troncQueteur->point_quete_id , $ulId );

    return  $troncQueteur;
  }


  public function getTroncQueteurFromTroncQueteurId(int $tronc_queteur_id, int $ulId )
  {
    $troncQueteur               = $this->troncQueteurDBService ->getTroncQueteurById($tronc_queteur_id            , $ulId );
    $troncQueteur->queteur      = $this->queteurDBService      ->getQueteurById     ($troncQueteur->queteur_id    , $ulId );
    $troncQueteur->point_quete  = $this->pointQueteDBService   ->getPointQueteById  ($troncQueteur->point_quete_id, $ulId );
    $troncQueteur->tronc        = $this->troncDBService        ->getTroncById       ($troncQueteur->tronc_id      , $ulId );

    return  $troncQueteur;
  }

}
