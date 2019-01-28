<?php
namespace RedCrossQuest\Entity;

use Google\Cloud\Logging\PsrLogger;

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
   * @param PsrLogger $logger
   */
  public function __construct(array $data, PsrLogger $logger)
  {
    parent::__construct($logger);

    $this->getInteger('secteur'         , $data);
    $this->getInteger('count'           , $data);
  }
}
