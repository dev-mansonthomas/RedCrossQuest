<?php
namespace RedCrossQuest\Entity;


use Psr\Log\LoggerInterface;

class CoinsMoneyBagSummaryEntity extends Entity
{

  public $amount       ;
  public $weight       ;
  public $total_euro2  ;
  public $total_euro1  ;
  public $total_cents50;
  public $total_cents20;
  public $total_cents10;
  public $total_cents5 ;
  public $total_cents2 ;
  public $total_cent1  ;
  public $count_euro2  ;
  public $count_euro1  ;
  public $count_cents50;
  public $count_cents20;
  public $count_cents10;
  public $count_cents5 ;
  public $count_cents2 ;
  public $count_cent1 ;

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
    * @throws \Exception if a parse Date or JSON fails
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
