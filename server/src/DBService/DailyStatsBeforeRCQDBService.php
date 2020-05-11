<?php
namespace RedCrossQuest\DBService;

require '../../vendor/autoload.php';

use DateInterval;
use DateTime;
use Exception;
use PDO;
use PDOException;
use RedCrossQuest\Entity\DailyStatsBeforeRCQEntity;
use RedCrossQuest\Service\Logger;

class DailyStatsBeforeRCQDBService extends DBService
{

  private $queteDates;


  public function __construct(array $queteDates, PDO $db, Logger $logger)
  {
    $this->queteDates = $queteDates;
    parent::__construct($db,$logger);
  }

  /**
   * return the date of the first day of the quete of the current year
   * @return string the date of the first day of the quete with the following format YYYY-MM-DD
   */
  public function getCurrentQueteStartDate():string
  {
    return $this->queteDates[date("Y")][0];
  }


  /**
   * Get all stats for UL $ulId and a particular year
   *
   * @param int     $ulId The ID of the Unite Locale
   * @param string  $year The year for which we wants the daily stats
   * @return DailyStatsBeforeRCQEntity[]  The PointQuete
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception if some parsing error occurs
   */
  public function getDailyStats(int $ulId, ?string $year):array
  {

    $parameters = ["ul_id" => $ulId];
    $yearSQL   = "";

    if($year != null)
    {
      $parameters["year"] = $year."%";
      $yearSQL = "AND   d.date  LIKE :year";
    }


    $sql = "
SELECT  d.`id`,
        d.`ul_id`,
        d.`date`,
        d.`amount`
FROM `daily_stats_before_rcq` AS d
WHERE d.ul_id = :ul_id
$yearSQL
ORDER BY d.date ASC
";


    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = new DailyStatsBeforeRCQEntity($row, $this->logger);
    }

    $stmt->closeCursor();

    return $results;
  }

  /**
   * update a daily stat (ie a particular day of a particular year)
   * @param DailyStatsBeforeRCQEntity $dailyStatsBeforeRCQEntity info about the dailyStats
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   */
  public function update(DailyStatsBeforeRCQEntity $dailyStatsBeforeRCQEntity, int $ulId):void
  {
    
    $sql ="
update  `daily_stats_before_rcq`
set     `amount`  = :amount
where   `id`      = :id
AND     `ul_id`   = :ulId   
";

    $stmt         = $this->db->prepare($sql);
    $stmt->execute([
      "amount"        => $dailyStatsBeforeRCQEntity->amount,
      "id"            => intVal($dailyStatsBeforeRCQEntity->id),
      "ulId"          => $ulId

    ]);

    $stmt->closeCursor();

  }

  /**
   * Create a year of daily data
   *
   * @param int    $ulId  Id of the UL for which we create the data
   * @param string $year  year to create
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception if something else fails
   */
  public function createYear(int $ulId, string $year):void
  {
    $sql = "
INSERT INTO `daily_stats_before_rcq`
(
  `ul_id`,
  `date`,
  `amount`
)
VALUES
(
  :ul_id,           
  :date,
  :amount
)
";
    $yearDefinition = $this->queteDates[$year];

    $startDate    = $yearDefinition[0];
    $numberOfDays = $yearDefinition[1];
    $oneDate      = DateTime::createFromFormat("Y-m-d", $startDate);

    $stmt         = $this->db->prepare($sql);

    for($i=0;$i<=$numberOfDays;$i++)
    {
      $stmt->execute([
        "ul_id"         => $ulId,
        "date"          => $oneDate->format("Y-m-d"),
        "amount"        => 0
      ]);

      $oneDate->add(new DateInterval('P1D'));

    }

    $stmt->closeCursor();
  }


  /**
   * Get the current number of DailyStats recorded for the Unite Local
   *
   * @param int           $ulId     Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the number of dailyStats
   * @throws PDOException if the query fails to execute on the server
   */
  public function getNumberOfDailyStats(int $ulId):int
  {
    $sql="
    SELECT count(1) as cnt
    FROM   daily_stats_before_rcq
    WHERE  ul_id = :ul_id
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(["ul_id" => $ulId]);
    $row = $stmt->fetch();
    return $row['cnt'];
  }

}
