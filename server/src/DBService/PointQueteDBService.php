<?php

namespace RedCrossQuest\DBService;

use \RedCrossQuest\Entity\PointQueteEntity;

class PointQueteDBService extends DBService
{


  /**
   * Get all pointQuete for UL $ulId
   *
   * @param int $ulId The ID of the Unite Locale
   * @return Array of PointQueteEntity  The PointQuete
   */
  public function getPointQuetes($ulId)
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
    pq.`created`
FROM `point_quete` AS pq
WHERE pq.id > 0
AND pq.ul_id = :ul_id
AND pq.enabled = 1
ORDER BY code ASC
";


    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute(["ul_id" => $ulId]);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch()) {
      $results[$i++] = new PointQueteEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }


  /**
   * Get one pointQuete by its ID
   *
   * @param int $point_quete_id The ID of the point_quete
   * @return PointQueteEntity  The PointQuete
   */
  public function getPointQueteById($point_quete_id, $ulId)
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
        pq.`created`
FROM `point_quete` AS pq
WHERE  pq.id    = :point_quete_id
AND    pq.ul_id = :ul_id";

    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute(["point_quete_id" => $point_quete_id, "ul_id" => $ulId]);

    if ($result) {
      $point_quete = new PointQueteEntity($stmt->fetch());
      $stmt->closeCursor();
      return $point_quete;
    }
  }
}
