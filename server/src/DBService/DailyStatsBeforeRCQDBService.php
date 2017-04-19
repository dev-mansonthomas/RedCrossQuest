<?php

namespace RedCrossQuest\DBService;

use \RedCrossQuest\Entity\DailyStatsBeforeRCQEntity;
use DateTime;
use DateInterval;

class DailyStatsBeforeRCQDBService extends DBService
{

  private static $queteDates = [
    "2004"=>["2004-05-15", 1], //fin le 16
    "2005"=>["2005-05-21", 1], //fin le 22
    "2006"=>["2006-05-20", 1], //fin le 21
    "2007"=>["2007-06-04", 6], //fin le 10
    "2008"=>["2008-05-12", 6], //fin le 18
    "2009"=>["2009-05-18", 6], //fin le 24
    "2010"=>["2010-06-05", 6], //fin le 11
    "2011"=>["2011-05-14", 7], //fin le 21
    "2012"=>["2012-06-02", 7], //fin le 09
    "2013"=>["2013-06-01", 8], //fin le 09
    "2014"=>["2014-05-24", 8], //fin le 01/06
    "2015"=>["2015-05-16", 8], //fin le 24
    "2016"=>["2016-05-28", 8], //fin le 05/06
    "2016"=>["2016-05-28", 8], //fin le 05/06
    "2017"=>["2017-06-10", 8], //fin le 18/06

  ];


  /**
   * Get all stats for UL $ulId and a particular year
   *
   * @param int $ulId The ID of the Unite Locale
   * @param
   * @return Array of PointQueteEntity  The PointQuete
   */
  public function getDailyStats($ulId, $year)
  {

    $sql = "
SELECT  d.`id`,
        d.`ul_id`,
        d.`date`,
        d.`amount`
FROM `daily_stats_before_rcq` AS d
WHERE d.ul_id = :ul_id
AND   d.date  LIKE :year
ORDER BY d.date DESC
";


    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute(["ul_id" => $ulId, "year" => $year."%"]);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch()) {
      $results[$i++] = new DailyStatsBeforeRCQEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }


  public function update($dailyStatsBeforeRCQEntity, $ulId)
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
      "ulId"         => $ulId

    ]);

    $stmt->closeCursor();


  }

  /**
   * Create a year of daily data
   *
   * @param $ulId : Id of the UL for which we create the data
   * @param $year : year to create
   */
  public function createYear($ulId, $year)
  {
    $this->logger->info('$year:', [$year]);


    $sql = "
INSERT INTO `rcq`.`daily_stats_before_rcq`
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
    $yearDefinition = DailyStatsBeforeRCQDBService::$queteDates[$year];



    $startDate    = $yearDefinition[0];
    $numberOfDays = $yearDefinition[1];
    $oneDate = DateTime::createFromFormat("Y-m-d", $startDate);

    $this->logger->info('$startDate:', [print_r($startDate,true)]);
    $this->logger->info('$oneDate:', [print_r($oneDate,true)]);
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

}
