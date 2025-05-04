<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use Exception;
use Psr\Log\LoggerInterface;

class UniteLocaleSettingsEntity  extends Entity
{
  public int $id;
  public int $ul_id;
  public mixed $settings;
  public Carbon $created;
  public Carbon $updated;
  public int $last_update_user_id;
  public string $token_benevole;
  public string $token_benevole_1j;

  protected array $_fieldList = ['id','ul_id','settings','created','updated','last_update_user_id','token_benevole','token_benevole_1j'];

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array &$data, LoggerInterface $logger)
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
