<?php
namespace RedCrossQuest\Entity;

use Google\Cloud\Logging\PsrLogger;

class SpotfireAccessEntity extends Entity
{
  public $id                          ;
  public $token                       ;
  public $token_expiration            ;

  public $ul_id                       ;
  public $user_id                     ;

  protected $_fieldList = ['id','token','token_expiration','ul_id','user_id'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param PsrLogger $logger
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, PsrLogger $logger)
  {
    parent::__construct($logger);
    $this->getInteger('id'                        , $data);
    $this->getString ('token'                     , $data, 36);
    $this->getDate   ('token_expiration'          , $data);
    $this->getInteger('ul_id'                     , $data);
    $this->getInteger('user_id'                   , $data);

  }
}
