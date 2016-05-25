<?php
namespace RedCrossQuest;

class PointQueteMapper extends Mapper
{
    public function getPointQuetes()
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
FROM `point_quete` as pq
WHERE pq.id > 0
ORDER BY code ASC
";


      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([]);

      $results = [];
      $i=0;
      while($row = $stmt->fetch())
      {
        $results[$i++] =  new PointQueteEntity($row);
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
    public function getPointQueteById($point_quete_id)
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
FROM `point_quete` as pq
WHERE  pq.id = :point_quete_id";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute(["point_quete_id" => $point_quete_id]);

        if($result)
        {
          $point_quete = new PointQueteEntity($stmt->fetch());
          $stmt->closeCursor();
          return $point_quete;
        }
    }
}
