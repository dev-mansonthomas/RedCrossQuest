<?php
namespace RedCrossQuest\Entity;

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

  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct(array $data)
    {
      $this->getString ('id'          , $data);
      $this->getString ('ul_id'       , $data);
      $this->getString ('created'     , $data);
      $this->getString ('enabled'     , $data);
      $this->getString ('notes'       , $data);
      $this->getInteger('type'        , $data);
      $this->getInteger('nombreTronc' , $data);


    }
}
