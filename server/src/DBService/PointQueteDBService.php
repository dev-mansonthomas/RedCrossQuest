<?php

namespace RedCrossQuest\DBService;

use \RedCrossQuest\Entity\PointQueteEntity;
use PDOException;

class PointQueteDBService extends DBService
{


  /**
   * Get all pointQuete for UL $ulId
   *
   * @param int $ulId The ID of the Unite Locale
   * @return PointQueteEntity[]  The list of PointQuete
   * @throws PDOException if the query fails to execute on the server
   */
  public function getPointQuetes(int $ulId)
  {

    $sql = "
SELECT pq.`id`,
    pq.`ul_id`,
    pq.`code`,
    pq.`name`,
    pq.`latitude`,
    pq.`longitude`,
    pq.`address`,
    pq.`postal_code`,
    pq.`city`,
    pq.`max_people`,
    pq.`advice`,
    pq.`localization`,
    pq.`minor_allowed`,
    pq.`created`,
    pq.`enabled`,
    pq.`type`,
    pq.`time_to_reach`,
    pq.`transport_to_reach`
FROM `point_quete` AS pq
WHERE pq.id > 0
AND pq.ul_id = :ul_id
AND pq.enabled = 1
ORDER BY type ASC, name ASC
";


    $stmt = $this->db->prepare($sql);
    $stmt->execute(["ul_id" => $ulId]);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = new PointQueteEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }


  /**
   * Get one pointQuete by its ID
   *
   * @param int $point_quete_id The ID of the point_quete
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return PointQueteEntity  The point quete info
   * @throws PDOException if the query fails to execute on the server
   */
  public function getPointQueteById(int $point_quete_id, int $ulId)
  {
    $sql = "
SELECT  pq.`id`,
        pq.`ul_id`,
        pq.`code`,
        pq.`name`,
        pq.`latitude`,
        pq.`longitude`,
        pq.`address`,
        pq.`postal_code`,
        pq.`city`,
        pq.`max_people`,
        pq.`advice`,
        pq.`localization`,
        pq.`minor_allowed`,
        pq.`created`,
        pq.`enabled`,
        pq.`type`,
        pq.`time_to_reach`,
        pq.`transport_to_reach`
FROM `point_quete` AS pq
WHERE  pq.id    = :point_quete_id
AND    pq.ul_id = :ul_id";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["point_quete_id" => $point_quete_id, "ul_id" => $ulId]);

    $point_quete = new PointQueteEntity($stmt->fetch());
    $stmt->closeCursor();
    return $point_quete;
  }
}
