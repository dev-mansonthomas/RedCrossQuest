<?php
namespace RedCrossQuest\Entity;

use Monolog\Logger;

class NamedDonationEntity extends Entity
{
  public $id               ;

  public $ul_id            ;

  public $ref_recu_fiscal  ;
  public $first_name       ;
  public $last_name        ;
  public $donation_date    ;
  public $address          ;
  public $postal_code      ;
  public $city             ;
  public $phone            ;
  public $email            ;

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

  public $notes             ;
  public $type              ;
  public $forme             ;

  public $deleted           ;


  public $coins_money_bag_id;
  public $bills_money_bag_id;

  public $last_update;
  public $last_update_user_id;

  protected $_fieldList = ['id','ul_id','ref_recu_fiscal','first_name','last_name','donation_date','address','postal_code','city','phone','email','euro500','euro200','euro100','euro50','euro20','euro10','euro5','euro2','euro1','cents50','cents20','cents10','cents5','cents2','cent1','don_cheque','don_creditcard','notes','type','forme','deleted','coins_money_bag_id','bills_money_bag_id','last_update','last_update_user_id'];


   /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     * @param Logger $logger
     * @throws \Exception if a parse Date or JSON fails
     */
    public function __construct(array $data, Logger $logger)
    {
      parent::__construct($logger);

      $this->getInteger('id'              , $data);
      $this->getInteger('ul_id'           , $data);

      $this->getInteger('ref_recu_fiscal'  , $data);
      $this->getString ('first_name'       , $data, 100);
      $this->getString ('last_name'        , $data, 100);
      $this->getDate   ('donation_date'    , $data);
      $this->getString ('address'          , $data, 200);
      $this->getInteger('postal_code'      , $data);
      $this->getString ('city'             , $data, 70);
      $this->getString ('phone'            , $data, 20);
      $this->getEmail  ('email'            , $data);

      $this->getInteger('euro500'          , $data);
      $this->getInteger('euro200'          , $data);
      $this->getInteger('euro100'          , $data);
      $this->getInteger('euro50'           , $data);
      $this->getInteger('euro20'           , $data);
      $this->getInteger('euro10'           , $data);
      $this->getInteger('euro5'            , $data);
      $this->getInteger('euro2'            , $data);
      $this->getInteger('euro1'            , $data);
      $this->getInteger('cents50'          , $data);
      $this->getInteger('cents20'          , $data);
      $this->getInteger('cents10'          , $data);
      $this->getInteger('cents5'           , $data);
      $this->getInteger('cents2'           , $data);
      $this->getInteger('cent1'            , $data);

      $this->getFloat  ('don_cheque'       , $data);
      $this->getFloat  ('don_creditcard'   , $data);
      

      $this->getString ('notes'            , $data, 500);
      $this->getInteger('type'             , $data);
      $this->getInteger('forme'            , $data);


      $this->getBoolean('deleted'             , $data);

      $this->getString('coins_money_bag_id'   , $data, 20);
      $this->getString('bills_money_bag_id'   , $data, 20);

      $this->getInteger('last_update_user_id' , $data);
      $this->getDate   ('last_update'         , $data);
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
        $this->checkPositive($this->don_creditcard) ;
    }

  /***
   * @param $value float the value to check
   * @return bool true if the value is > 0
   */
    function checkPositive($value)
    {
      return $value != null && $value > 0;
    }
}
