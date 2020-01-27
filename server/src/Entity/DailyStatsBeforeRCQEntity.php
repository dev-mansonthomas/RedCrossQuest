<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="DailyStatsBeforeRCQEntity", required={"id", "ul_id", "date", "amount"})
 */
class DailyStatsBeforeRCQEntity  extends Entity
{
  /**
   * @OA\Property()
   * @var integer $id Id of the stat
   */
  public $id           ;
  /**
   * @OA\Property()
   * @var integer $ul_id UL ID of the stat
   */
  public $ul_id        ;
  /**
   * @OA\Property()
   * @var Carbon $date The day of the stats
   */
  public $date         ;
  /**
   * @OA\Property()
   * @var float $amount total amount of money collected on that day
   */
  public $amount       ;

  protected $_fieldList = ['id', 'ul_id', 'date', 'amount'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, LoggerInterface $logger)
  {
    parent::__construct($logger);

    $this->getInteger('id'           , $data);
    $this->getInteger('ul_id'        , $data);
    $this->getDate   ('date'         , $data);
    $this->getFloat  ('amount'       , $data);
  }
}
