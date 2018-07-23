<?php
namespace RedCrossQuest\Entity;

class UniteLocaleSettingsEntity  extends Entity
{
  public $id;
  public $ul_id;
  public $settings;
  public $created;
  public $updated;
  public $last_update_user_id;


  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   */
  public function __construct(array $data)
  {
    $this->getInteger ('id'                      , $data);
    $this->getInteger ('ul_id'                   , $data);
    $this->getString  ('settings'                , $data);
    $this->getString  ('created'                 , $data);
    $this->getString  ('updated'                 , $data);
    $this->getInteger ('last_update_user_id'     , $data);
  }
}
