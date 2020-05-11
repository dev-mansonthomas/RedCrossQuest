<?php
namespace RedCrossQuest\Entity;

use Exception;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="CoinsMoneyBagSummaryEntity", required={"type"})
 */
class CoinsMoneyBagSummaryEntity extends Entity
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
   * @var float $total_euro2 Sum in € of coins of 2€
   */
  public $total_euro2  ;
  /**
   * @OA\Property()
   * @var float $total_euro1 Sum in € of coins of 1€
   */
  public $total_euro1  ;
  /**
   * @OA\Property()
   * @var float $total_cents50 Sum in € of coins of 50cts
   */
  public $total_cents50;
  /**
   * @OA\Property()
   * @var float $total_cents20 Sum in € of coins of 20cts
   */
  public $total_cents20;
  /**
   * @OA\Property()
   * @var float $total_cents10 Sum in € of coins of 10cts
   */
  public $total_cents10;
  /**
   * @OA\Property()
   * @var float $total_cents5 Sum in € of coins of 5cts
   */
  public $total_cents5 ;
  /**
   * @OA\Property()
   * @var float $total_cents2 Sum in € of coins of 2cts
   */
  public $total_cents2 ;
  /**
   * @OA\Property()
   * @var float $total_cent1 Sum in € of coins of 1ct
   */
  public $total_cent1  ;
  /**
   * @OA\Property()
   * @var int $count_euro2 Number of coins of 2€
   */
  public $count_euro2  ;
  /**
   * @OA\Property()
   * @var int $count_euro1 Number of coins of 1€
   */
  public $count_euro1  ;
  /**
   * @OA\Property()
   * @var int $count_cents50 Number of coins of 50cts
   */
  public $count_cents50;
  /**
   * @OA\Property()
   * @var int $count_cents20 Number of coins of 20cts
   */
  public $count_cents20;
  /**
   * @OA\Property()
   * @var int $count_cents10 Number of coins of 10cts
   */
  public $count_cents10;
  /**
   * @OA\Property()
   * @var int $count_cents5 Number of coins of 5cts
   */
  public $count_cents5 ;
  /**
   * @OA\Property()
   * @var int $count_cents2 Number of coins of 2cts
   */
  public $count_cents2 ;
  /**
   * @OA\Property()
   * @var int $count_cent1 Number of coins of 1ct
   */
  public $count_cent1 ;

  /**
   * @OA\Property()
   * @var string $coins_money_bag_id The ID of the MoneyBag
   */
  public $coins_money_bag_id;


  protected $_fieldList = [
    'total_euro2'   ,
    'total_euro1'   ,
    'total_cents50' ,
    'total_cents20' ,
    'total_cents10' ,
    'total_cents5'  ,
    'total_cents2'  ,
    'total_cent1'   ,
    'count_euro2'   ,
    'count_euro1'   ,
    'count_cents50' ,
    'count_cents20' ,
    'count_cents10' ,
    'count_cents5'  ,
    'count_cents2'  ,
    'count_cent1'   ,
    'amount'        ,
    'weight'        ,
    'coins_money_bag_id'
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
      $this->getFloat  ('total_euro2'  , $data);
      $this->getFloat  ('total_euro1'  , $data);
      $this->getFloat  ('total_cents50', $data);
      $this->getFloat  ('total_cents20', $data);
      $this->getFloat  ('total_cents10', $data);
      $this->getFloat  ('total_cents5' , $data);
      $this->getFloat  ('total_cents2' , $data);
      $this->getFloat  ('total_cent1'  , $data);
      $this->getInteger('count_euro2'  , $data);
      $this->getInteger('count_euro1'  , $data);
      $this->getInteger('count_cents50', $data);
      $this->getInteger('count_cents20', $data);
      $this->getInteger('count_cents10', $data);
      $this->getInteger('count_cents5' , $data);
      $this->getInteger('count_cents2' , $data);
      $this->getInteger('count_cent1'  , $data);

      $this->getString('coins_money_bag_id', $data, 20);
    }
}
