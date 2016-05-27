<?php
namespace RedCrossQuest;

class TroncMapper extends Mapper
{
    public function getTroncs($query=null)
    {
//TODO ajouter un filtre sur l'UL en cours
      $sql = "
SELECT `id`,
       `ul_id`,
       `created`,
       `enabled`,
       `notes`
FROM   `tronc` as t
";
      if($query != null)
      {
        $sql .="
        WHERE CONVERT(id, CHAR) like concat(:query,'%')
        AND enabled = 1;
";
      }
      else
      {
        $sql .="
        WHERE enabled = 1;
";
      }

      $sql .="
      ORDER BY id ASC
";
      $this->logger->debug($sql);
      $stmt = $this->db->prepare($sql);
      if($query != null)
      {
        $result = $stmt->execute([ "query" => $query]);
      }
      else
      {
        $result = $stmt->execute([]);
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
 * search all tronc acording to a query type
 *
 * 0 : All troncs
 * 1 : enabled troncs
 * 2 : disabled troncs
 */
  public function getTroncsBySearchType($searchType)
  {

    $sql = "
SELECT 	`id`,
        `ul_id`,
        `created`,
        `enabled`,
        `notes`
FROM    `tronc` as t
";

    if($searchType == "1")
    {
      $sql .= "
WHERE t.enabled = 1
";
    }
    else
    {
      $sql .= "
WHERE t.enabled = 0
";

    }

    $sql .="
ORDER BY id ASC
    ";
    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute([]);

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
     * @return TroncEntity  The tronc
     */
    public function getTroncById($tronc_id)
    {
      $sql = "
SELECT `id`,
       `ul_id`,
       `created`,
       `enabled`,
       `notes`
FROM  `tronc` as t
WHERE  t.id = :tronc_id
";

      $stmt = $this->db->prepare($sql);

      $result = $stmt->execute(["tronc_id" => $tronc_id]);

      if($result && $stmt->rowCount() == 1 )
      {
        $tronc = new TroncEntity($stmt->fetch());
        $stmt->closeCursor();
        return $tronc;
      }
      else
      {
        throw new \Exception("Tronc with ID:'".$tronc_id . "' not found");
      }
    }


  /**
   * Update one tronc
   *
   * @param TroncEntity $tronc The tronc to update
   * @return TroncEntity  The tronc
   */
    public function update(TroncEntity $tronc)
    {
      $sql = "
UPDATE `tronc`
SET
  `notes`       = :notes,
  `ul_id`       = :ul_id,
  `enabled`     = :enabled
WHERE `id` = :id;
";

      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        "notes"      => $tronc->notes,
        "ul_id"      => $tronc->ul_id,
        "enabled"    => $tronc->enabled,
        "id"         => $tronc->id
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
   * @return int the primary key of the new tronc
   */
  public function insert(TroncEntity $tronc)
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
      "ul_id"    => $tronc->ul_id,
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
