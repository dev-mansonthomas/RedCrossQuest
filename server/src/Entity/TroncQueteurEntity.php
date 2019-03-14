<?php
namespace RedCrossQuest\Entity;

use Google\Cloud\Logging\PsrLogger;

class TroncQueteurEntity extends Entity
{
  /***
   * ID of the tronc_queteur
   * or
   * ID of the row of the tronc_queteur_historique when fetching the history of the tronc_queteur.
   * The ID of tronc_queteur is stored in tronc_queteur_id column
   */
  public $id               ;
  public $ul_id            ;
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

  public $notes_depart_theorique      ;
  public $notes_retour                ;
  public $notes_retour_comptage_pieces;
  public $notes_update                ;

  //only used when getting tronc_queteur for a tronc
  public $last_name        ;
  public $first_name       ;

  public $deleted          ;

  //when this object is used to retrieve data from tronc_queteur_historique
  public $tronc_queteur_id;
  public $insert_date     ;

  public $preparationAndDepart;

  public $coins_money_bag_id;
  public $bills_money_bag_id;

  public $don_cb_total_number       ;
  public $don_cheque_number         ;


  //export data UL
  public $amount;
  public $weight;
  public $time_spent_in_hours;


  protected $_fieldList = ['id','queteur_id','queteur','point_quete','point_quete_id','tronc_id','depart_theorique','depart','retour','comptage','last_update','last_update_user_id','euro500','euro200','euro100','euro50','euro20','euro10','euro5','euro2','euro1','cents50','cents20','cents10','cents5','cents2','cent1','don_cheque','don_creditcard','foreign_coins','foreign_banknote','notes_depart_theorique','notes_retour','notes_retour_comptage_pieces','notes_update','last_name','first_name','deleted','tronc_queteur_id','insert_date','preparationAndDepart','coins_money_bag_id','bills_money_bag_id','don_cb_sans_contact_amount','don_cb_sans_contact_number','don_cb_total_number','don_cheque_number','amount','weight','time_spent_in_hours'];

   /**
    * Accept an array of data matching properties of this class
    * and create the class
    *
    * @param array $data The data to use to create
    * @param PsrLogger $logger
    * @throws \Exception if a parse Date or JSON fails
    */
    public function __construct(array $data, PsrLogger $logger)
    {
      parent::__construct($logger);

      $this->getInteger('id'                          , $data);
      $this->getInteger('ul_id'                       , $data);
      $this->getInteger('queteur_id'                  , $data);
      $this->getInteger('point_quete_id'              , $data);
      $this->getInteger('tronc_id'                    , $data);
      $this->getDate   ('depart_theorique'            , $data);

      //$this->logger->info("Date before/after",array("before"=>$data['depart_theorique'],"after"=>$this->depart_theorique));

      $this->getDate   ('depart'                      , $data);
      $this->getDate   ('retour'                      , $data);
      $this->getDate   ('comptage'                    , $data);
      $this->getDate   ('last_update'                 , $data);
      $this->getInteger('last_update_user_id'         , $data);

      $this->getInteger('euro500'                     , $data);
      $this->getInteger('euro200'                     , $data);
      $this->getInteger('euro100'                     , $data);
      $this->getInteger('euro50'                      , $data);
      $this->getInteger('euro20'                      , $data);
      $this->getInteger('euro10'                      , $data);
      $this->getInteger('euro5'                       , $data);
      $this->getInteger('euro2'                       , $data);
      $this->getInteger('euro1'                       , $data);
      $this->getInteger('cents50'                     , $data);
      $this->getInteger('cents20'                     , $data);
      $this->getInteger('cents10'                     , $data);
      $this->getInteger('cents5'                      , $data);
      $this->getInteger('cents2'                      , $data);
      $this->getInteger('cent1'                       , $data);

      $this->getFloat  ('don_cheque'                  , $data);
      $this->getFloat  ('don_creditcard'              , $data);
      

      $this->getInteger('foreign_coins'                , $data);
      $this->getInteger('foreign_banknote'             , $data);

      $this->getString('notes_depart_theorique'       , $data, 500);
      $this->getString('notes_retour'                 , $data, 500);
      $this->getString('notes_retour_comptage_pieces' , $data, 500);
      $this->getString('notes_update'                 , $data, 500);

      $this->getString('last_name'                    , $data, 100);
      $this->getString('first_name'                   , $data, 100);

      $this->getBoolean('deleted'                     , $data);

      $this->getInteger('tronc_queteur_id'            , $data);
      $this->getDate   ('insert_date'                 , $data);

      $this->getBoolean('preparationAndDepart'        , $data);

      $this->getString ('coins_money_bag_id'          , $data, 20);
      $this->getString ('bills_money_bag_id'          , $data, 20);

      $this->getInteger('rowCount'                    , $data);

      $this->getInteger('don_cb_total_number'         , $data);
      $this->getInteger('don_cheque_number'           , $data);


      $this->getFloat  ('amount'                      , $data);
      $this->getFloat  ('weight'                      , $data);
      $this->getFloat  ('time_spent_in_hours'         , $data);
    }

  /***
   * check if some money information has been filled
   * @return bool true if at least one bill or one coin or don_cheque or don_cb is > 0
   */
    function isMoneyFilled()
    {
      return
        $this->checkPositive($this->euro500       ) ||
        $this->checkPositive($this->euro200       ) ||
        $this->checkPositive($this->euro100       ) ||
        $this->checkPositive($this->euro50        ) ||
        $this->checkPositive($this->euro20        ) ||
        $this->checkPositive($this->euro10        ) ||
        $this->checkPositive($this->euro5         ) ||
        $this->checkPositive($this->euro2         ) ||
        $this->checkPositive($this->euro1         ) ||
        $this->checkPositive($this->cents50       ) ||
        $this->checkPositive($this->cents20       ) ||
        $this->checkPositive($this->cents10       ) ||
        $this->checkPositive($this->cents5        ) ||
        $this->checkPositive($this->cents2        ) ||
        $this->checkPositive($this->cent1         ) ||
        $this->checkPositive($this->don_cheque    ) ||
        $this->checkPositive($this->don_creditcard) ||
        $this->checkPositive($this->foreign_coins ) ||
        $this->checkPositive($this->foreign_banknote );
    }

  /***
   * @param $value float the value to check
   * @return bool true if the value is > 0
   */
    function checkPositive($value)
    {
      return $value != null && $value > 0;
    }

    /**
     * Prepare the object to be published to PubSub (final target is BigQuery)
     * Unset unwanted variables, they are those that don't have a definition in BigQuery
     * Change the dateTime format
     *
     * @return TroncQueteurEntity the object itself
     */
    function prepareForPublish()
    {
      $this->depart_theorique = $this->depart_theorique != null ? $this->depart_theorique ->toDateTimeString() : null;
      $this->depart           = $this->depart           != null ? $this->depart           ->toDateTimeString() : null;
      $this->retour           = $this->retour           != null ? $this->retour           ->toDateTimeString() : null;
      $this->comptage         = $this->comptage         != null ? $this->comptage         ->toDateTimeString() : null;
      $this->last_update      = $this->last_update      != null ? $this->last_update      ->toDateTimeString() : null;

      unset($this->queteur              );
      unset($this->point_quete          );
      unset($this->tronc_queteur_id     );
      unset($this->insert_date          );
      unset($this->preparationAndDepart );
      unset($this->amount               );
      unset($this->weight               );
      unset($this->time_spent_in_hours  );
      unset($this->first_name           );
      unset($this->last_name            );
      unset($this->clientInputValidator );


      return $this;
    }
}
