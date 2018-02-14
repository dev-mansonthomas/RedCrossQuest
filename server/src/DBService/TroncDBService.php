<?php
namespace RedCrossQuest\DBService;

use \RedCrossQuest\Entity\TroncEntity;
use PDOException;

class TroncDBService extends DBService
{

  /**
   * search all tronc, that are enabled, if query is specified, search on the ID
   * @param string  $query    search query
   * @param boolean $active   search active or incative troncs
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return TroncEntity[] list of troncs
   * @throws PDOException if the query fails to execute on the server
   *
   */
    public function getTroncs(string $query=null, int $ulId, bool $active )
    {
      $sql = "
SELECT `id`,
       `ul_id`,
       `created`,
       `enabled`,
       `notes`,
       `type`
FROM   `tronc` as t
WHERE enabled = :enabled
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
      //$this->logger->debug($sql);
      $stmt = $this->db->prepare($sql);
      if($query != null)
      {
        $stmt->execute([ "query" => $query, "ul_id" => $ulId, 'enabled'=>$active ]);
      }
      else
      {
        $stmt->execute(["ul_id" => $ulId, 'enabled'=>$active ]);
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
 *
 * @param int $searchType
 * 0 : All troncs
 * 1 : enabled troncs
 * 2 : disabled troncs
 *
 * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
 *
 * @return TroncEntity[] array of troncs
 * @throws PDOException if the query fails to execute on the server
 */
  public function getTroncsBySearchType(int $searchType, int $ulId)
  {

    $sql = "
SELECT 	`id`,
        `ul_id`,
        `created`,
        `enabled`,
        `notes`,
        `type`
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

    $stmt->execute(["ul_id" => $ulId]);

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
     * @throws PDOException if the query fails to execute on the server
     */
    public function getTroncById(int $tronc_id, int $ulId)
    {
      $sql = "
SELECT `id`,
       `ul_id`,
       `created`,
       `enabled`,
       `notes`,
       `type`
FROM  `tronc` as t
WHERE  t.id    = :tronc_id
AND    t.ul_id = :ul_id
";

      $stmt = $this->db->prepare($sql);
      $stmt->execute(["tronc_id" => $tronc_id, "ul_id" => $ulId]);

      if($stmt->rowCount() == 1 )
      {
        $tronc = new TroncEntity($stmt->fetch());
        $stmt->closeCursor();
        return $tronc;
      }
      else
      {
        $stmt->closeCursor();
        throw new \Exception("Tronc with ID:'".$tronc_id . "' for ul with id: $ulId not found");
      }
    }


  /**
   * Update one tronc
   *
   * @param TroncEntity $tronc The tronc to update
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   */
    public function update(TroncEntity $tronc, int $ulId)
    {
      $sql = "
UPDATE `tronc`
SET
      `notes`       = :notes,
      `enabled`     = :enabled,
      `type`        = :type
WHERE `id`          = :id
AND   `ul_id`       = :ul_id
";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        "notes"      => $tronc->notes,
        "enabled"    => $tronc->enabled,
        "id"         => $tronc->id,
        "type"         => $tronc->type,
        "ul_id"      => $ulId
      ]);

      //$this->logger->warning($stmt->rowCount());
      $stmt->closeCursor();
    }

  /**
   * Insert one Tronc
   *
   * @param TroncEntity $tronc The tronc to update
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the primary key of the new tronc
   * @throws PDOException if the query fails to execute on the server
   */
  public function insert(TroncEntity $tronc, int $ulId)
  {
    $sql = "
INSERT INTO `tronc`
(
   `ul_id`,
   `created`,
   `enabled`,
   `notes`,
   `type`
)
VALUES
(
  :ul_id,
  NOW(),
  :enabled,
  :notes,
  :type
);
";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $stmt->execute([
      "ul_id"    => $ulId,
      "enabled"  => $tronc->enabled,
      "notes"    => $tronc->notes,
      "type"    => $tronc->type
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row  = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];
    //$this->logger->info('$lastInsertId:', [$lastInsertId]) ;

    $stmt->closeCursor();
    $this->db->commit();
    return $lastInsertId;
  }

  /**
   * Get the current number of Troncs for the Unite Local
   *
   * @param int           $ulId     Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the number of troncs
   * @throws PDOException if the query fails to execute on the server
   */
  public function getNumberOfTroncs(int $ulId)
  {
    $sql="
    SELECT count(1) as cnt
    FROM   tronc
    WHERE  ul_id = :ul_id
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(["ul_id" => $ulId]);
    $row = $stmt->fetch();
    return $row['cnt'];
  }
}
