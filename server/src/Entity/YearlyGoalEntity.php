<?php
namespace RedCrossQuest\Entity;

use Exception;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;
/**
 * @OA\Schema(schema="YearlyGoalEntity", required={"id","ul_id","year","amount","day_1_percentage","day_2_percentage","day_3_percentage","day_4_percentage","day_5_percentage","day_6_percentage","day_7_percentage","day_8_percentage","day_9_percentage"})
 */
class YearlyGoalEntity  extends Entity
{
  /**
   * @OA\Property()
   * @var int $id user Id
   */
  public int $id           ;
  /**
   * @OA\Property()
   * @var int $ul_id Yearly Goals UL's id
   */
  public int $ul_id        ;
  /**
   * @OA\Property()
   * @var int $year The year of this goals
   */
  public int $year         ;
  /**
   * @OA\Property()
   * @var float $amount The total amount of money that is targeted to be raised
   */
  public float $amount       ;
  /**
   * @OA\Property()
   * @var int $day_1_percentage percentage of the total amount of money that should be raised on day 1
   */
  public int $day_1_percentage;
  /**
   * @OA\Property()
   * @var int $day_2_percentage percentage of the total amount of money that should be raised on day 2
   */
  public int $day_2_percentage;
  /**
   * @OA\Property()
   * @var int $day_3_percentage percentage of the total amount of money that should be raised on day 3
   */
  public int $day_3_percentage;
  /**
   * @OA\Property()
   * @var int $day_4_percentage percentage of the total amount of money that should be raised on day 4
   */
  public int $day_4_percentage;
  /**
   * @OA\Property()
   * @var int $day_5_percentage percentage of the total amount of money that should be raised on day 5
   */
  public int $day_5_percentage;
  /**
   * @OA\Property()
   * @var int $day_6_percentage percentage of the total amount of money that should be raised on day 6
   */
  public int $day_6_percentage;
  /**
   * @OA\Property()
   * @var int $day_7_percentage percentage of the total amount of money that should be raised on day 7
   */
  public int $day_7_percentage;
  /**
   * @OA\Property()
   * @var int $day_8_percentage percentage of the total amount of money that should be raised on day 8
   */
  public int $day_8_percentage;
  /**
   * @OA\Property()
   * @var int $day_9_percentage percentage of the total amount of money that should be raised on day 9
   */
  public int $day_9_percentage;

  protected array $_fieldList = ['id','ul_id','year','amount','day_1_percentage','day_2_percentage','day_3_percentage','day_4_percentage','day_5_percentage','day_6_percentage','day_7_percentage','day_8_percentage','day_9_percentage'];

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
    $this->getInteger('id'               , $data);
    $this->getInteger('ul_id'            , $data);
    $this->getInteger('year'             , $data);
    $this->getInteger('amount'           , $data);

    $this->getInteger('day_1_percentage' , $data);
    $this->getInteger('day_2_percentage' , $data);
    $this->getInteger('day_3_percentage' , $data);
    $this->getInteger('day_4_percentage' , $data);
    $this->getInteger('day_5_percentage' , $data);
    $this->getInteger('day_6_percentage' , $data);
    $this->getInteger('day_7_percentage' , $data);
    $this->getInteger('day_8_percentage' , $data);
    $this->getInteger('day_9_percentage' , $data);
  }
}
