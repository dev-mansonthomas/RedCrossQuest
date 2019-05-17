<?php
namespace RedCrossQuest\Entity;


use RedCrossQuest\Service\Logger;

class MailingSummaryEntity extends Entity
{
  public $secteur          ;
  public $count            ;

  protected $_fieldList = ['secteur', 'count'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param Logger $logger
   */
  public function __construct(array $data, Logger $logger)
  {
    parent::__construct($logger);

    $this->getInteger('secteur'         , $data);
    $this->getInteger('count'           , $data);
  }
}
