<?php
namespace RedCrossQuest\Entity;

class TroncQueteurEntity extends Entity
{
  /***
   * ID of the tronc_queteur
   * or
   * ID of the row of the tronc_queteur_historique when fetching the history of the tronc_queteur.
   * The ID of tronc_queteur is stored in tronc_queteur_id column
   */
  public $id               ;
  public $queteur_id       ;
  /**
   * Full queteur object, initialized by $routes.php under some circumstances
   */
  public $queteur;

  /**
   * Full point_quete object, initialized by $routes.php under some circumstances
   */
  public $point_quete;

  public $point_quete_id   ;
  public $tronc_id         ;
  public $depart_theorique ;
  public $depart           ;
  public $retour           ;
  public $comptage         ;
  public $last_update      ;
  public $last_update_user_id;

  public $euro500          ;
  public $euro200          ;
  public $euro100          ;
  public $euro50           ;
  public $euro20           ;
  public $euro10           ;
  public $euro5            ;
  public $euro2            ;
  public $euro1            ;
  public $cents50          ;
  public $cents20          ;
  public $cents10          ;
  public $cents5           ;
  public $cents2           ;
  public $cent1            ;
  public $don_cheque       ;
  public $don_creditcard   ;

  public $foreign_coins    ;
  public $foreign_banknote ;
  public $notes            ;

  //only used when getting tronc_queteur for a tronc
  public $last_name        ;
  public $first_name       ;


  //when this object is used to retrieve data from tronc_queteur_historique
  public $tronc_queteur_id;
  public $insert_date;

  protected $logger;

  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct($data, $logger)
    {
      $this->logger = $logger;

      $this->getString('id'               , $data);
      $this->getString('queteur_id'       , $data);
      $this->getString('point_quete_id'   , $data);
      $this->getString('tronc_id'         , $data);
      $this->getDate  ('depart_theorique' , $data);
      $this->getDate  ('depart'           , $data);
      $this->getDate  ('retour'           , $data);
      $this->getDate  ('comptage'         , $data);
      $this->getDate  ('last_update'      , $data);
      $this->getInteger('last_update_user_id', $data);

      $this->getString('euro500'          , $data);
      $this->getString('euro200'          , $data);
      $this->getString('euro100'          , $data);
      $this->getString('euro50'           , $data);
      $this->getString('euro20'           , $data);
      $this->getString('euro10'           , $data);
      $this->getString('euro5'            , $data);
      $this->getString('euro2'            , $data);
      $this->getString('euro1'            , $data);
      $this->getString('cents50'          , $data);
      $this->getString('cents20'          , $data);
      $this->getString('cents10'          , $data);
      $this->getString('cents5'           , $data);
      $this->getString('cents2'           , $data);
      $this->getString('cent1'            , $data);

      $this->getString('don_cheque'       , $data);
      $this->getString('don_creditcard'   , $data);
      

      $this->getString('foreign_coins'    , $data);
      $this->getString('foreign_banknote' , $data);

      $this->getString('notes_depart_theorique' , $data);
      $this->getString('notes_retour'           , $data);
      $this->getString('notes_retour_comptage_pieces', $data);
      $this->getString('notes_update'           , $data);

      $this->getString('last_name'              , $data);
      $this->getString('first_name'             , $data);

      $this->getBoolean('deleted'               , $data);

      $this->getString('tronc_queteur_id'       , $data);
      $this->getDate  ('insert_date'            , $data);


    }
}
