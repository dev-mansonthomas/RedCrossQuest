<?php
namespace RedCrossQuest\Entity;

use RedCrossQuest\Service\Logger;

class UniteLocaleSettingsEntity  extends Entity
{
  public $id;
  public $ul_id;
  public $settings;
  public $created;
  public $updated;
  public $last_update_user_id;
  public $token_benevole;
  public $token_benevole_1j;

  protected $_fieldList = ['id','ul_id','settings','created','updated','last_update_user_id','token_benevole','token_benevole_1j'];

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
    $this->getInteger ('id'                      , $data);
    $this->getInteger ('ul_id'                   , $data);
    $this->getJson    ('settings'                , $data, 4000);
    $this->getDate    ('created'                 , $data);
    $this->getDate    ('updated'                 , $data);
    $this->getInteger ('last_update_user_id'     , $data);

    $this->getString  ('token_benevole'          , $data, 36);
    $this->getString  ('token_benevole_1j'       , $data, 36);

  }
}
