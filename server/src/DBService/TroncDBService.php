<?php
namespace RedCrossQuest\DBService;

require '../../vendor/autoload.php';

use PDOException;
use RedCrossQuest\Entity\TroncEntity;

class TroncDBService extends DBService
{

  /**
   * search all tronc, that are enabled, if query is specified, search on the ID
   * @param string  $query    search query
   * @param boolean $active   search active or incative troncs, if null, all troncs are returned
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $type  Id of the type of tronc, if null, search all types.
   * @return TroncEntity[] list of troncs
   * @throws PDOException if the query fails to execute on the server
   * @throws \Exception in other situations, possibly : parsing error in the entity
   *
   */
    public function getTroncs(?string $query, int $ulId, ?bool $active, ?int $type )
    {

      $parameters = ["ul_id"  => $ulId];
      $sql = "
SELECT `id`,
       `ul_id`,
       `created`,
       `enabled`,
       `notes`,
       `type`
FROM   `tronc` as t
WHERE  t.ul_id = :ul_id
";

      if($active != null)
      {
        $parameters[ "enabled"] = $active===true?"1":"0";
        $sql .="
        AND    enabled = :enabled
";
      }

      if($query != null)
      {
        $parameters[ "query"] =$query;
        $sql .="
        AND CONVERT(id, CHAR) like concat(:query,'%')
";
      }

      if( $type != null)
      {
        $parameters[ "type"] =$type;
        $sql .="
        AND `type` = :type
";
      }


      $sql .="
      ORDER BY id ASC
";
      //$this->logger->info($sql, $parameters);
      $stmt = $this->db->prepare($sql);
      $stmt->execute($parameters);


      $results = [];
      $i=0;
      while($row = $stmt->fetch())
      {
        $results[$i++] =  new TroncEntity($row, $this->logger);
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
        $tronc = new TroncEntity($stmt->fetch(), $this->logger);
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
        "enabled"    => $tronc->enabled===true?"1":"0",
        "id"         => $tronc->id,
        "type"       => $tronc->type,
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
";
    if($tronc->nombreTronc > 50 || !is_int($tronc->nombreTronc))
    {
      throw new \InvalidArgumentException("Invalid number of tronc to be created ".$tronc->nombreTronc);
    }
    for($i=0;$i<$tronc->nombreTronc;$i++)
    {
      $sql .="(:ul_id, NOW(), :enabled, :notes, :type)".($i<$tronc->nombreTronc-1?",":"");
    }

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      "ul_id"    => $ulId,
      "enabled"  => $tronc->enabled===true?"1":"0",
      "notes"    => $tronc->notes,
      "type"     => $tronc->type
    ]);

    $stmt->closeCursor();

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


  /**
   * Mark All Troncs as printed
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   */
  public function markAllAsPrinted(int $ulId)
  {

    $sql = "
UPDATE `tronc`
SET    `qr_code_printed` = 1
WHERE  `ul_id`           = :ul_id
";
    $parameters["ul_id"] = $ulId;

    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);
    $stmt->closeCursor();

  }
}
