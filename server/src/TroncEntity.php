<?php
namespace RedCrossQuest;

class TroncEntity
{
  public $id      ;
  public $ul_id   ;
  public $created ;
  public $enabled ;
  public $notes   ;

  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct($data)
    {
      if(array_key_exists('id', $data))
      {
        $this->id                     = $data['id'];
      }
      $this->ul_id                    = $data['ul_id'];
      if(array_key_exists('created', $data))
      {
        $this->created                = $data['created'];
      }
      $this->enabled                  = $data['enabled'];

      if(array_key_exists('notes', $data))
      {
        $this->notes = $data['notes'];
      }
    }
}
