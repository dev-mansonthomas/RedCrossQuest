<?php
namespace RedCrossQuest\DBService;

use \RedCrossQuest\Entity\TroncEntity;
use Symfony\Component\Config\Definition\Exception\Exception;

class TroncDBService extends DBService
{

  /**
   * search all tronc, that are enabled, if query is specified, search on the ID
   * @param int $query
   * @param int    $ulId  : the ID of the UniteLocal on which the search is limited
   * @return list of QueteurEntity
   *
   */
    public function getTroncs($query=null, $ulId)
    {
      $sql = "
SELECT `id`,
       `ul_id`,
       `created`,
       `enabled`,
       `notes`
FROM   `tronc` as t
WHERE enabled = 1
AND   t.ul_id = :ul_id
";
      if($query != null)
      {
        $sql .="
AND CONVERT(id, CHAR) like concat(:query,'%')
";
      }

      $sql .="
      ORDER BY id ASC
";
      $this->logger->debug($sql);
      $stmt = $this->db->prepare($sql);
      if($query != null)
      {
        $result = $stmt->execute([ "query" => $query, "ul_id" => $ulId]);
      }
      else
      {
        $result = $stmt->execute(["ul_id" => $ulId]);
      }


      $results = [];
      $i=0;
      while($row = $stmt->fetch())
      {
        $results[$i++] =  new TroncEntity($row);
      }

      $stmt->closeCursor();
      return $results;
    }


/**
 * search all tronc according to a query type
 * @param int $searchType
 * 0 : All troncs
 * 1 : enabled troncs
 * 2 : disabled troncs
 *@param $ulId
 * @return list of tronc
 */
  public function getTroncsBySearchType($searchType, $ulId)
  {

    $sql = "
SELECT 	`id`,
        `ul_id`,
        `created`,
        `enabled`,
        `notes`
FROM    `tronc` as t
WHERE  t.ul_id = :ul_id
";

    if($searchType == "1")
    {
      $sql .= "
AND t.enabled = 1
";
    }
    else
    {
      $sql .= "
AND t.enabled = 0
";

    }

    $sql .="
ORDER BY id ASC
    ";
    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute(["ul_id" => $ulId]);

    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new TroncEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }



    /**
     * Get one tronc by its ID
     *
     * @param int $tronc_id The ID of the tronc
     * @param int $ulId the ID of the UniteLocal
     * @return TroncEntity  The tronc
     * @throws \Exception if the tronc is not found
     */
    public function getTroncById($tronc_id, $ulId)
    {
      $sql = "
SELECT `id`,
       `ul_id`,
       `created`,
       `enabled`,
       `notes`
FROM  `tronc` as t
WHERE  t.id    = :tronc_id
AND    t.ul_id = :ul_id
";

      $stmt = $this->db->prepare($sql);

      $result = $stmt->execute(["tronc_id" => $tronc_id, "ul_id" => $ulId]);

      if($result && $stmt->rowCount() == 1 )
      {
        $tronc = new TroncEntity($stmt->fetch());
        $stmt->closeCursor();
        return $tronc;
      }
      else
      {
        throw new \Exception("Tronc with ID:'".$tronc_id . "' for ul with id: $ulId not found");
      }
    }


  /**
   * Update one tronc
   *
   * @param TroncEntity $tronc The tronc to update
   * @param int $ulId the ID of the UniteLocal
   * @return TroncEntity  The tronc
   */
    public function update(TroncEntity $tronc, $ulId)
    {
      $sql = "
UPDATE `tronc`
SET
  `notes`       = :notes,
  `enabled`     = :enabled
WHERE `id`  = :id
AND   ul_id = :ul_id
";

      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        "notes"      => $tronc->notes,
        "enabled"    => $tronc->enabled,
        "id"         => $tronc->id,
        "ul_id"      => $ulId
      ]);

      $this->logger->warning($stmt->rowCount());
      $stmt->closeCursor();

      if(!$result) {
          throw new Exception("could not save record");
      }
    }



  /**
   * Insert one Tronc
   *
   * @param TroncEntity $tronc The tronc to update
   * @param int $ulId the ID of the UniteLocal
   * @return int the primary key of the new tronc
   */
  public function insert(TroncEntity $tronc, $ulId)
  {
    $sql = "
INSERT INTO `tronc`
(
   `ul_id`,
   `created`,
   `enabled`,
   `notes`
)
VALUES
(
  :ul_id,
  NOW(),
  :enabled,
  :notes
);
";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $result = $stmt->execute([
      "ul_id"    => $ulId,
      "enabled"  => $tronc->enabled,
      "notes"    => $tronc->notes
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row  = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];
    $this->logger->info('$lastInsertId:', [$lastInsertId]) ;

    $stmt->closeCursor();
    $this->db->commit();
    return $lastInsertId;
  }
}
