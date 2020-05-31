<?php
namespace RedCrossQuest\DBService;

use Exception;
use PDOException;
use RedCrossQuest\Entity\YearlyGoalEntity;

class YearlyGoalDBService extends DBService
{
  /**
   * Get all goals for UL $ulId and a particular year
   *
   * @param int     $ulId The ID of the Unite Locale
   * @param string  $year The year for which we wants the yearly goals
   * @return YearlyGoalEntity  The YearlyGoalEntity
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getYearlyGoals(int $ulId, string $year):?YearlyGoalEntity
  {
    $sql = "
SELECT  y.`id`,
        y.`ul_id`,
        y.`year`,
        y.`amount`,
        y.`day_1_percentage`,
        y.`day_2_percentage`,
        y.`day_3_percentage`,
        y.`day_4_percentage`,
        y.`day_5_percentage`,
        y.`day_6_percentage`,
        y.`day_7_percentage`,
        y.`day_8_percentage`,
        y.`day_9_percentage`
FROM `yearly_goal` AS y
WHERE y.ul_id = :ul_id
AND   y.year  = :year
";
    $parameters = ["ul_id" => $ulId, "year" => $year];
    
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new YearlyGoalEntity($row, $this->logger);
    }, false);
  }

  /**
   * update a yearly goal
   * @param YearlyGoalEntity $yearlyGoalEntity info about the dailyStats
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function update(YearlyGoalEntity $yearlyGoalEntity, int $ulId):void
  {
    $sql ="
update  `yearly_goal`
set     
`amount`           = :amount,
`day_1_percentage` = :day_1_percentage,
`day_2_percentage` = :day_2_percentage,
`day_3_percentage` = :day_3_percentage,
`day_4_percentage` = :day_4_percentage,
`day_5_percentage` = :day_5_percentage,
`day_6_percentage` = :day_6_percentage,
`day_7_percentage` = :day_7_percentage,
`day_8_percentage` = :day_8_percentage,
`day_9_percentage` = :day_9_percentage
where   `id`       = :id
AND     `ul_id`    = :ulId   
";
    $parameters = [
      "amount"            => $yearlyGoalEntity->amount,
      "day_1_percentage"  => $yearlyGoalEntity->day_1_percentage,
      "day_2_percentage"  => $yearlyGoalEntity->day_2_percentage,
      "day_3_percentage"  => $yearlyGoalEntity->day_3_percentage,
      "day_4_percentage"  => $yearlyGoalEntity->day_4_percentage,
      "day_5_percentage"  => $yearlyGoalEntity->day_5_percentage,
      "day_6_percentage"  => $yearlyGoalEntity->day_6_percentage,
      "day_7_percentage"  => $yearlyGoalEntity->day_7_percentage,
      "day_8_percentage"  => $yearlyGoalEntity->day_8_percentage,
      "day_9_percentage"  => $yearlyGoalEntity->day_9_percentage,
      "id"                => $yearlyGoalEntity->id,
      "ulId"              => $ulId
    ];
    $this->executeQueryForUpdate($sql, $parameters);
  }

  /**
   * Create a year of goal
   *
   * @param int $ulId Id of the UL for which we create the data
   * @param string $year year to create
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function createYear(int $ulId, string $year):void
  {
    $sql = "
INSERT INTO `yearly_goal`
(
  `ul_id`,
  `year`,
  `amount`,
  `day_1_percentage`,
  `day_2_percentage`,
  `day_3_percentage`,
  `day_4_percentage`,
  `day_5_percentage`,
  `day_6_percentage`,
  `day_7_percentage`,
  `day_8_percentage`,
  `day_9_percentage`
)
VALUES
(
  :ul_id,           
  :year,
  :amount,
  :day_1_percentage,
  :day_2_percentage,
  :day_3_percentage,
  :day_4_percentage,
  :day_5_percentage,
  :day_6_percentage,
  :day_7_percentage,
  :day_8_percentage,
  :day_9_percentage 
)
";
    $parameters = [
      "ul_id"            => $ulId,
      "year"             => $year,
      "amount"           => 0,
      "day_1_percentage" => 30,
      "day_2_percentage" => 15,
      "day_3_percentage" => 6,
      "day_4_percentage" => 4,
      "day_5_percentage" => 6,
      "day_6_percentage" => 6,
      "day_7_percentage" => 8,
      "day_8_percentage" => 15,
      "day_9_percentage" => 10
    ];

    $this->executeQueryForInsert($sql, $parameters, false);
  }


  /**
   * Get the current number of YearlyGoals recorded for this year for the Unite Local
   *
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the number of dailyStats
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function getNumberOfYearlyGoals(int $ulId):int
  {
    $sql="
    SELECT 1
    FROM   yearly_goal
    WHERE  ul_id = :ul_id
    AND    year = year(now())
    ";

    $parameters = ["ul_id" => $ulId];
    return $this->getCountForSQLQuery($sql, $parameters);
  }


  /**
   * Get all goals for UL $ulId (and if specified, a particular year, all if not)
   *
   * @param int     $ulId The ID of the Unite Locale
   * @param string  $year The year for which we wants the yearly goals
   * @return YearlyGoalEntity[]  The YearlyGoalEntity
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getYearlyGoalsForExportData(int $ulId, ?string $year):array
  {
    $parameters = ["ul_id" => $ulId];
    $yearSQL="";
    if($year != null)
    {
      $yearSQL="AND   y.year  = :year";
      $parameters["year"] = $year;
    }


    $sql = "
SELECT  y.`id`,
        y.`ul_id`,
        y.`year`,
        y.`amount`,
        y.`day_1_percentage`,
        y.`day_2_percentage`,
        y.`day_3_percentage`,
        y.`day_4_percentage`,
        y.`day_5_percentage`,
        y.`day_6_percentage`,
        y.`day_7_percentage`,
        y.`day_8_percentage`,
        y.`day_9_percentage`
FROM `yearly_goal` AS y
WHERE y.ul_id = :ul_id
$yearSQL
ORDER BY y.id ASC
";
    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new YearlyGoalEntity($row, $this->logger);
    });
  }
}
