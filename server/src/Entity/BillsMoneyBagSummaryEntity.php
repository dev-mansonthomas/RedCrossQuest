<?php
namespace RedCrossQuest\Entity;

use Exception;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="BillsMoneyBagSummaryEntity", required={"type"})
 */
class BillsMoneyBagSummaryEntity extends Entity
{
  /**
   * @OA\Property()
   * @var float $amount total amount of money in the bag
   */
  public $amount       ;
  /**
   * @OA\Property()
   * @var float $weight total weight of money in the bag
   */
  public $weight       ;
  /**
   * @OA\Property()
   * @var int $total_euro2 Sum in € of coins of 5€
   */
  public $total_euro5  ;
  /**
   * @OA\Property()
   * @var int $total_euro10 Sum in € of coins of 10€
   */
  public $total_euro10 ;
  /**
   * @OA\Property()
   * @var int $total_euro20 Sum in € of coins of 20€
   */
  public $total_euro20 ;
  /**
   * @OA\Property()
   * @var int $total_euro50 Sum in € of coins of 50€
   */
  public $total_euro50 ;
  /**
   * @OA\Property()
   * @var int $total_euro100 Sum in € of coins of 100€
   */
  public $total_euro100;
  /**
   * @OA\Property()
   * @var int $total_euro200 Sum in € of coins of 200€
   */
  public $total_euro200;
  /**
   * @OA\Property()
   * @var int $total_euro500 Sum in € of coins of 500€
   */
  public $total_euro500;
  /**
   * @OA\Property()
   * @var int $count_euro5 Count of bills of 5€
   */
  public $count_euro5  ;
  /**
   * @OA\Property()
   * @var int $count_euro10 Count of bills of 10€
   */
  public $count_euro10 ;
  /**
   * @OA\Property()
   * @var int $count_euro20 Count of bills of 20€
   */
  public $count_euro20 ;
  /**
   * @OA\Property()
   * @var int $count_euro50 Count of bills of 50€
   */
  public $count_euro50 ;
  /**
   * @OA\Property()
   * @var int $count_euro100 Count of bills of 100€
   */
  public $count_euro100;
  /**
   * @OA\Property()
   * @var int $count_euro200 Count of bills of 200€
   */
  public $count_euro200;
  /**
   * @OA\Property()
   * @var int $count_euro500 Count of bills of 500€
   */
  public $count_euro500;

  /**
   * @OA\Property()
   * @var string $bills_money_bag_id The ID of the MoneyBag
   */
  public $bills_money_bag_id;


  protected $_fieldList = [
    'total_euro5'   ,
    'total_euro10'  ,
    'total_euro20'  ,
    'total_euro50'  ,
    'total_euro100' ,
    'total_euro200' ,
    'total_euro500' ,
    'count_euro5'   ,
    'count_euro10'  ,
    'count_euro20'  ,
    'count_euro50'  ,
    'count_euro100' ,
    'count_euro200' ,
    'count_euro500' ,
    'amount'        ,
    'weight'        ,
    'bills_money_bag_id'
  ];

   /**
    * Accept an array of data matching properties of this class
    * and create the class
    *
    * @param array $data The data to use to create
    * @param LoggerInterface $logger
    * @throws Exception if a parse Date or JSON fails
    */
    public function __construct(array $data, LoggerInterface $logger)
    {
      parent::__construct($logger);


      $this->getFloat  ('amount'       , $data);
      $this->getFloat  ('weight'       , $data);
      $this->getFloat  ('total_euro5'  , $data);
      $this->getFloat  ('total_euro10' , $data);
      $this->getFloat  ('total_euro20' , $data);
      $this->getFloat  ('total_euro50' , $data);
      $this->getFloat  ('total_euro100', $data);
      $this->getFloat  ('total_euro200', $data);
      $this->getFloat  ('total_euro500', $data);
      $this->getInteger('count_euro5'  , $data);
      $this->getInteger('count_euro10' , $data);
      $this->getInteger('count_euro20' , $data);
      $this->getInteger('count_euro50' , $data);
      $this->getInteger('count_euro100', $data);
      $this->getInteger('count_euro200', $data);
      $this->getInteger('count_euro500', $data);

      $this->getString('bills_money_bag_id', $data, 20);
    }
}
