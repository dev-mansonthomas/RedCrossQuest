<?php
namespace RedCrossQuest\Entity;


use Psr\Log\LoggerInterface;

class BillsMoneyBagSummaryEntity extends Entity
{

  public $amount       ;
  public $weight       ;
  public $total_euro5  ;
  public $total_euro10 ;
  public $total_euro20 ;
  public $total_euro50 ;
  public $total_euro100;
  public $total_euro200;
  public $total_euro500;
  public $count_euro5  ;
  public $count_euro10 ;
  public $count_euro20 ;
  public $count_euro50 ;
  public $count_euro100;
  public $count_euro200;
  public $count_euro500;

  public $coins_money_bag_id;


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
