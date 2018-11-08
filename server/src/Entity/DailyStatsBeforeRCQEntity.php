<?php
namespace RedCrossQuest\Entity;

use Monolog\Logger;

class DailyStatsBeforeRCQEntity  extends Entity
{
  public $id           ;
  public $ul_id        ;
  public $date         ;
  public $amount       ;

  protected $_fieldList = ['id', 'ul_id', 'date', 'amount'];
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

    $this->getString('id'           , $data);
    $this->getString('ul_id'        , $data);
    $this->getDate  ('date'         , $data);
    $this->getString('amount'       , $data);
  }
}
