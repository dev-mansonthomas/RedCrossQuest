<?php
namespace RedCrossQuest\Entity;


use Carbon\Carbon;
use Exception;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;


/**
 * @OA\Schema(schema="TroncQueteurEntity", required={"ul_id"})
 */

class TroncQueteurEntity extends Entity
{
  /***
   * ID of the tronc_queteur
   * or
   * ID of the row of the tronc_queteur_historique when fetching the history of the tronc_queteur.
   * The ID of tronc_queteur is stored in tronc_queteur_id column
   */

  /**
   * @OA\Property()
   * @var ?int $id TroncQueteur ID
   */
  public ?int $id               ;
  /**
   * @OA\Property()
   * @var ?int $ul_id UL ID
   */
  public ?int $ul_id            ;
  /**
   * @OA\Property()
   * @var ?int $queteur_id queteur ID
   */
  public ?int $queteur_id       ;
  /**
   * @OA\Property(
   *     property="queteur",
   *          ref="#/components/schemas/QueteurEntity"
   * )
   * @var ?QueteurEntity $queteur Full queteur object, initialized by $routes.php under some circumstances
   */
  public ?QueteurEntity $queteur;
  
  /**
   * @OA\Property(
   *     property="point_quete",
   *          ref="#/components/schemas/PointQueteEntity"
   * )
   * @var ?PointQueteEntity $point_quete Full point_quete object, initialized by $routes.php under some circumstances
   */
  public ?PointQueteEntity $point_quete;

  /**
   * @OA\Property(
   *     property="tronc",
   *          ref="#/components/schemas/TroncEntity"
   * )
   * @var ?TroncEntity $tronc Full tronc object, initialized by $routes.php under some circumstances
   */
  public ?TroncEntity $tronc;

  public ?int $rowCount;

  /**
   * @OA\Property()
   * @var ?int $point_quete_id Point de Quete ID
   */
  public ?int $point_quete_id   ;
  /**
   * @OA\Property()
   * @var ?int $tronc_id Tronc ID
   */
  public ?int $tronc_id         ;

  /**
   * string type : see $this->preparePubSubPublishing()
   * @OA\Property()
   * @var Carbon|string|null $depart_theorique theoretical Departure Date of the volunteer
   */
  public Carbon|string|null $depart_theorique ;
  /**
   * string type : see $this->preparePubSubPublishing()
   * @OA\Property()
   * @var Carbon|string|null $depart Real departure date
   */
  public Carbon|string|null $depart    = null;
  /**
   *  string type : see $this->preparePubSubPublishing()
   * @OA\Property()
   * @var Carbon|string|null $retour Return date
   */
  public Carbon|string|null $retour           ;
  /**
   *  string type : see $this->preparePubSubPublishing()
   * @OA\Property()
   * @var Carbon|string|null $comptage Coins & Bills counting date
   */
  public Carbon|string|null $comptage         ;
  /**
   * @OA\Property()
   * @var Carbon|string|null $last_update Last time the TroncQueteur row is updated
   */
  public Carbon|string|null $last_update      ;
  /**
   *  string type : see $this->preparePubSubPublishing()
   * @OA\Property()
   * @var ?int $last_update_user_id UserId of the user that performed the last update on this object
   */
  public ?int $last_update_user_id;
  /**
   * @OA\Property()
   * @var ?int $euro500 Number of 500€ bills
   */
  public ?int $euro500          ;
  /**
   * @OA\Property()
   * @var ?int $euro200 Number of 200€ bills
   */
  public ?int $euro200          ;
  /**
   * @OA\Property()
   * @var ?int $euro100 Number of 100€ bills
   */
  public ?int $euro100          ;
  /**
   * @OA\Property()
   * @var ?int $euro50 Number of 50€ bills
   */
  public ?int $euro50           ;
  /**
   * @OA\Property()
   * @var ?int $euro20 Number of 20€ bills
   */
  public ?int $euro20           ;
  /**
   * @OA\Property()
   * @var ?int $euro10 Number of 10€ bills
   */
  public ?int $euro10           ;
  /**
   * @OA\Property()
   * @var ?int $euro5 Number of 5€ bills
   */
  public ?int $euro5            ;
  /**
   * @OA\Property()
   * @var ?int $euro2 Number of 2€ coins
   */
  public ?int $euro2            ;
  /**
   * @OA\Property()
   * @var ?int $euro1 Number of 1€ coins
   */
  public ?int $euro1            ;
  /**
   * @OA\Property()
   * @var ?int $cents50 Number of 50cts coins
   */
  public ?int $cents50          ;
  /**
   * @OA\Property()
   * @var ?int $cents20 Number of 20cts coins
   */
  public ?int $cents20          ;
  /**
   * @OA\Property()
   * @var ?int $cents10 Number of 10cts coins
   */
  public ?int $cents10          ;
  /**
   * @OA\Property()
   * @var ?int $cents5 Number of 5cts coins
   */
  public ?int $cents5           ;
  /**
   * @OA\Property()
   * @var ?int $cents2 Number of 2cts coins
   */
  public ?int $cents2           ;
  /**
   * @OA\Property()
   * @var ?int $cent1 Number of 1ct coins
   */
  public ?int $cent1            ;
  /**
   * @OA\Property()
   * @var ?float|null $don_cheque total amount of bank note collected
   */
  public ?float $don_cheque       ;
  /**
   * @OA\Property()
   * @var ?float|null $don_cheque total amount of credit card payment collected
   */
  public ?float $don_creditcard   ;
  /**
   * @OA\Property(deprecated=true)
   * @var ?int $foreign_coins Number of foreign coins
   */
  public ?int $foreign_coins    ;
  /**
   * @OA\Property(deprecated=true)
   * @var ?int $foreign_banknote Number of foreign bills
   */
  public ?int $foreign_banknote ;
  /**
   * @OA\Property()
   * @var ?string $notes_depart_theorique Textual notes about the start
   */
  public ?string $notes_depart_theorique      ;
  /**
   * @OA\Property()
   * @var ?string $notes_depart_theorique Textual notes about the return
   */
  public ?string $notes_retour                ;
  /**
   * @OA\Property()
   * @var ?string $notes_retour_comptage_pieces Textual notes about the counting of money
   */
  public ?string $notes_retour_comptage_pieces;
  /**
   * @OA\Property()
   * @var ?string $notes_update Textual notes about the update of a tronc
   */
  public ?string $notes_update                ;

  /**
   * @OA\Property()
   * @var ?string $last_name Queteur Last Name  (only used when getting tronc_queteur for a tronc)
   */
  public ?string $last_name        ;
  /**
   * @OA\Property()
   * @var ?string $first_name Queteur first name  (only used when getting tronc_queteur for a tronc)
   */
  public ?string $first_name       ;
  /**
   * @OA\Property()
   * @var ?boolean $deleted if true, the troncQueteur is marked as deleted and does not count in the stats.
   */
  public ?bool $deleted          ;
  /**
   * @OA\Property()
   * @var ?int $tronc_queteur_id TroncQueteur ID (when this object is used to retrieve data from tronc_queteur_historique, this ID refers to the current row in tronc_queteur table)
   */
  public ?int $tronc_queteur_id;
  /**
   * @OA\Property()
   * @var ?Carbon $insert_date When this historic version of the TroncQueteur has been inserted (when this object is used to retrieve data from tronc_queteur_historique)
   */
  public ?Carbon $insert_date     ;
  /**
   * @OA\Property()
   * @var ?boolean $preparationAndDepart If it's a preparation and depart (that is: the tronc_queteur is stored and the depart is right now) this property must be set to true.
   */
  public ?bool $preparationAndDepart;
  /**
   * @OA\Property()
   * @var ?string $coins_money_bag_id Identifier of the bag that contains the coins of this troncQueteur. It's used to track the total amount and weight of the bag. The amount must be exact to avoid bank penalty. The bank is also setting limits so that the bag is not teared apart with an excess of weight.
   */
  public ?string $coins_money_bag_id;
  /**
   * @OA\Property()
   * @var ?string $bills_money_bag_id Identifier of the bag that contains the bills of this troncQueteur. It's used to track the total amount and weight of the bag. The amount must be exact to avoid bank penalty. The bank is also setting limits so that the bag is not teared apart with an excess of weight.
   */
  public ?string $bills_money_bag_id;
  /**
   * @OA\Property()
   * @var ?int $don_cb_total_number Number of donation per credit card
   */
  public ?int $don_cb_total_number       ;
  /**
   * @OA\Property()
   * @var ?int $don_cheque_number Number of donation per bank note
   */
  public ?int $don_cheque_number         ;

  /**
   * @OA\Property()
   * @var CreditCardEntity[]|null $don_cb_details The details of credit card donations
   */
  public ?array $don_cb_details = null      ;


  /**
   * @OA\Property()
   * @var ?float|null $amount Sum in € of coins, bill, credit card & bank note. Used when Extracting Data of an UL.
   */
  public ?float $amount;
  /**
   * @OA\Property()
   * @var ?float|null $weight Sum in kg of coins, bill. Used when Extracting Data of an UL. Used when Extracting Data of an UL.
   */
  public ?float $weight;
  /**
   * @OA\Property()
   * @var ?float|null $time_spent_in_hours number of hours spent on the street collecting money. Used when Extracting Data of an UL.
   */
  public ?float $time_spent_in_hours;


  //when searching for a tronc on Depart screen
  /**
   * @OA\Property()
   * @var ?boolean $troncFromPreviousYear if true, the troncQueteur retrieved by the scan of the Tronc QRCode (or tronc id input) retrieve a tronc from the previous year.when searching for a tronc on Depart screen
   */
  public ?bool $troncFromPreviousYear;
  /**
   * @OA\Property()
   * @var ?boolean $troncQueteurIsInAnIncorrectState  the current state of the tronc is not compatible with a Depart. when searching for a tronc on Depart screen
   */
  public ?bool $troncQueteurIsInAnIncorrectState;
  /**
   * @OA\Property()
   * @var ?boolean $queteHasNotStartedYet Flag set to true if a user tries to prepare a tronc for a date that is before the official start of the quête.
   */
  public ?bool $queteHasNotStartedYet;
  /**
   * @OA\Property()
   * @var ?boolean $departAlreadyRegistered the depart has already been recorded. The QRCode scan multiple time per second (sometime with incorrect reading, so we can't slow it down)
   */
  public ?bool $departAlreadyRegistered;


  protected array $_fieldList = ['id','queteur_id','queteur','point_quete','point_quete_id','tronc_id','depart_theorique','depart','retour','comptage','last_update','last_update_user_id','euro500','euro200','euro100','euro50','euro20','euro10','euro5','euro2','euro1','cents50','cents20','cents10','cents5','cents2','cent1','don_cheque','don_creditcard','foreign_coins','foreign_banknote','notes_depart_theorique','notes_retour','notes_retour_comptage_pieces','notes_update','last_name','first_name','deleted','tronc_queteur_id','insert_date','preparationAndDepart','coins_money_bag_id','bills_money_bag_id','don_cb_total_number','don_cheque_number','amount','weight','time_spent_in_hours', 'troncFromPreviousYear', 'troncQueteurIsInAnIncorrectState', 'queteHasNotStartedYet','departAlreadyRegistered', 'don_cb_details'];

   /**
    * Accept an array of data matching properties of this class
    * and create the class
    *
    * @param array $data The data to use to create
    * @param LoggerInterface $logger
    * @throws Exception if a parse Date or JSON fails
    */
    public function __construct(array &$data, LoggerInterface $logger)
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

      if( array_key_exists('don_cb_details',$data))
      {
        $this->don_cb_details=array();
        foreach ($data['don_cb_details'] as $don_cb_detail) {
          $this->don_cb_details[]=new CreditCardEntity($don_cb_detail, $this->logger);
        }
      }
    }

  /***
   * check if some money information has been filled
   * @return bool true if at least one bill or one coin or don_cheque or don_cb is > 0
   */
    function isMoneyFilled():bool
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
    function checkPositive(float $value):bool
    {
      return $value != null && $value > 0;
    }

    /**
     * Prepare the object to be published to PubSub (final target is BigQuery)
     * Unset unwanted variables, they are those that don't have a definition in BigQuery
     * Change the dateTime format
     */
    function preparePubSubPublishing():void
    {
      //$this->logger->error("DEBUG 1", ["tq"=>print_r($this, true)]);
      $this->depart_theorique = $this->depart_theorique ?->toDateTimeString();
      $this->depart           = $this->depart           ?->toDateTimeString();
      $this->retour           = $this->retour           ?->toDateTimeString();
      $this->comptage         = $this->comptage         ?->toDateTimeString();
      $this->last_update      = $this->last_update      ?->toDateTimeString();

      unset($this->tronc);
      unset($this->rowCount);
      //$this->logger->error("DEBUG 2", ["tq"=>print_r($this, true)]);
      $this->genericPreparePubSubPublishing();
      //$this->logger->error("DEBUG 3", ["tq"=>print_r($this, true)]);
    }
}
