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

  public function __construct($logger, $troncQueteurMapper, $queteurMapper, $pointQueteMapper)
  {
    $this->logger = $logger;

    $this->troncQueteurMapper = $troncQueteurMapper;
    $this->queteurMapper      = $queteurMapper;
    $this->pointQueteMapper   = $pointQueteMapper;
  }
  
  public function getLastTroncQueteurFromTroncId($tronc_id)
  {
    $troncQueteur               = $this->troncQueteurMapper ->getLastTroncQueteurByTroncId($tronc_id);
    $troncQueteur->queteur      = $this->queteurMapper      ->getQueteurById              ($troncQueteur->queteur_id);
    $troncQueteur->point_quete  = $this->pointQueteMapper   ->getPointQueteById           ($troncQueteur->point_quete_id);

    return  $troncQueteur;
  }

}
