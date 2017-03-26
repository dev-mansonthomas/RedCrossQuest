<?php
namespace RedCrossQuest;
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 25/05/2016
 * Time: 10:45
 */





class TroncQueteurAction
{
  protected $logger;
  protected $troncQueteurMapper;
  protected $queteurMapper     ;
  protected $pointQueteMapper  ;

  public function __construct($logger,
                              $troncQueteurMapper ,
                              $queteurMapper      ,
                              $pointQueteMapper   ,
                              $troncMapper        )
  {
    $this->logger = $logger;

    $this->troncQueteurMapper = $troncQueteurMapper;
    $this->queteurMapper      = $queteurMapper;
    $this->pointQueteMapper   = $pointQueteMapper;
    $this->troncMapper        = $troncMapper;
  }
  
  public function getLastTroncQueteurFromTroncId($tronc_id)
  {
    $troncQueteur               = $this->troncQueteurMapper ->getLastTroncQueteurByTroncId($tronc_id);
    $troncQueteur->queteur      = $this->queteurMapper      ->getQueteurById              ($troncQueteur->queteur_id);
    $troncQueteur->point_quete  = $this->pointQueteMapper   ->getPointQueteById           ($troncQueteur->point_quete_id);

    return  $troncQueteur;
  }


  public function getTroncQueteurFromTroncQueteurId($tronc_queteur_id)
  {
    $troncQueteur               = $this->troncQueteurMapper ->getTroncQueteurById($tronc_queteur_id);
    $troncQueteur->queteur      = $this->queteurMapper      ->getQueteurById     ($troncQueteur->queteur_id);
    $troncQueteur->point_quete  = $this->pointQueteMapper   ->getPointQueteById  ($troncQueteur->point_quete_id);
    $troncQueteur->tronc        = $this->troncMapper        ->getTroncById       ($troncQueteur->tronc_id);

    return  $troncQueteur;
  }

}
