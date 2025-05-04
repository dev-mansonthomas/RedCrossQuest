<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use Exception;
use OpenApi\Annotations as OA;
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
  public int $id           ;
  /**
   * @OA\Property()
   * @var integer $ul_id UL ID of the stat
   */
  public int $ul_id        ;
  /**
   * @OA\Property()
   * @var Carbon $date The day of the stats
   */
  public Carbon $date         ;
  /**
   * @OA\Property()
   * @var float $amount total amount of money collected on that day
   */
  public float $amount       ;

  /**
   * @OA\Property()
   * @var int $nb_benevole Number of volunteers involved in the quete for that day
   */
  public int $nb_benevole       ;

  /**
   * @OA\Property()
   * @var int $nb_benevole_1j Number of 1day volunteers involved in the quete for that day
   */
  public int $nb_benevole_1j       ;

  /**
   * @OA\Property()
   * @var int $nb_heure Number of cumulated hours of quete for that day
   */
  public int $nb_heure       ;


  protected array $_fieldList = ['id', 'ul_id', 'date', 'amount', 'nb_benevole', 'nb_benevole_1j', 'nb_heure'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array &$data, LoggerInterface $logger)
  {
    parent::__construct($logger);

    $this->getInteger('id'            , $data);
    $this->getInteger('ul_id'         , $data, 0);
    $this->getDate   ('date'          , $data);
    $this->getFloat  ('amount'        , $data);
    $this->getInteger('nb_benevole'   , $data, 0);
    $this->getInteger('nb_benevole_1j', $data, 0);
    $this->getInteger('nb_heure'      , $data, 0);
  }
}
