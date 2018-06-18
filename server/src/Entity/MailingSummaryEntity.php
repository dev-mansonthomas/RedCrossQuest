<?php
namespace RedCrossQuest\Entity;

use Monolog\Logger;

class MailingSummaryEntity extends Entity
{
  public $ul_id            ;
  public $secteur          ;
  public $count            ;

  protected $logger;

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param Logger $logger
   */
  public function __construct(array $data, Logger $logger)
  {
    $this->logger = $logger;

    $this->getInteger('ul_id'           , $data);
    $this->getInteger('secteur'         , $data);
    $this->getInteger('count'           , $data);
  }
}
