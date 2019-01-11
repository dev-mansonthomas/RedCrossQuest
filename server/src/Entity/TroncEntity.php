<?php
namespace RedCrossQuest\Entity;
use Monolog\Logger;

class TroncEntity extends Entity
{
  public $id      ;
  public $ul_id   ;
  public $created ;
  public $enabled ;
  public $notes   ;
  public $type    ;
  /** utiliser seulement à la création*/
  public $nombreTronc;

  protected $_fieldList = ['id','ul_id','created','enabled','notes','nombreTronc'];
  /**
     * Accept an array of data matching properties of this class
     * and create the class
   * @param array $data The data to use to create
   * @param Logger $logger
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, Logger $logger)
  {
    parent::__construct($logger);
      $this->getString ('id'          , $data);
      $this->getString ('ul_id'       , $data);
      $this->getString ('created'     , $data);
      $this->getBoolean('enabled'     , $data);
      $this->getString ('notes'       , $data);
      $this->getInteger('type'        , $data);
      $this->getInteger('nombreTronc' , $data);


    }
}
