<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;

class DailyStatsBeforeRCQEntity  extends Entity
{
  public $id           ;
  public $ul_id        ;
  public $date         ;
  public $amount       ;

  /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
  public function __construct($data)
  {
    $this->getString('id'           , $data);
    $this->getString('ul_id'        , $data);
    $this->getDate  ('date'         , $data);
    $this->getString('amount'       , $data);

  }
}
