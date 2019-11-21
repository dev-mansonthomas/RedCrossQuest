<?php
namespace RedCrossQuest\Entity;


use Psr\Log\LoggerInterface;

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
   * @param LoggerInterface $logger
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, LoggerInterface $logger)
  {
    parent::__construct($logger);

    $this->getInteger('id'           , $data);
    $this->getInteger('ul_id'        , $data);
    $this->getDate   ('date'         , $data);
    $this->getFloat  ('amount'       , $data);
  }
}
