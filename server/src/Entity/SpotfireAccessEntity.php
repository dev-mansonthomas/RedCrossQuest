<?php
namespace RedCrossQuest\Entity;

class SpotfireAccessEntity extends Entity
{
  public $id                          ;
  public $token                       ;
  public $token_expiration            ;

  public $ul_id                       ;
  public $user_id                     ;


  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
  public function __construct($data)
  {
    $this->getInteger('id'                        , $data);
    $this->getString ('token'                     , $data);
    $this->getDate   ('token_expiration'          , $data);
    $this->getInteger('ul_id'                     , $data);
    $this->getInteger('user_id'                   , $data);

  }
}
