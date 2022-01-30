<?php
namespace RedCrossQuest\DBService;

use Exception;
use InvalidArgumentException;
use PDOException;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\Entity\PageableResponseEntity;
use RedCrossQuest\Entity\TroncEntity;

class TroncDBService extends DBService
{

  /**
   * search all tronc, that are enabled, if query is specified, search on the ID
   * @param PageableRequestEntity  $pageableRequestEntity
   *     string  $query    search query
   *     boolean $active   search active or incative troncs, if null, all troncs are returned
   *     int $type  Id of the type of tronc, if null, search all types.
   * @param  int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return PageableResponseEntity The response with the count of rows
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   *
   */
//?string $query, ?bool $active, ?int $type
  public function getTroncs(PageableRequestEntity $pageableRequestEntity, int $ulId):PageableResponseEntity
    {
      /** @var string $query */
      $query  = $pageableRequestEntity->filterMap['q'];
      /** @var bool $active */
      $active = $pageableRequestEntity->filterMap['active'];
      /** @var int $type */
      $type   = $pageableRequestEntity->filterMap['type'];

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

      if($active !==   null)
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
      $count   = $this->getCountForSQLQuery ($sql, $parameters);
      $results = $this->executeQueryForArray($sql, $parameters, function($row) {
        return new TroncEntity($row, $this->logger);
      }, $pageableRequestEntity->pageNumber, $pageableRequestEntity->rowsPerPage);

      return new PageableResponseEntity($count, $results, $pageableRequestEntity->pageNumber, $pageableRequestEntity->rowsPerPage);
    }

    
    /**
     * Get one tronc by its ID
     *
     * @param int $tronc_id The ID of the tronc
     * @param int $ulId the ID of the UniteLocal
     * @return TroncEntity|null  The tronc
     * @throws Exception if the tronc is not found
     * @throws PDOException if the query fails to execute on the server
     */
    public function getTroncById(int $tronc_id, int $ulId):?TroncEntity
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
LIMIT 1
";
      $parameters = ["tronc_id" => $tronc_id, "ul_id" => $ulId];

      /** @noinspection PhpIncompatibleReturnTypeInspection */
      return $this->executeQueryForObject($sql, $parameters, function($row) {
        return new TroncEntity($row, $this->logger);
      }, false);
    }


  /**
   * Update one tronc
   *
   * @param TroncEntity $tronc The tronc to update
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
    public function update(TroncEntity $tronc, int $ulId):void
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
      $parameters = [
        "notes"      => $tronc->notes,
        "enabled"    => $tronc->enabled===true?"1":"0",
        "id"         => $tronc->id,
        "type"       => $tronc->type,
        "ul_id"      => $ulId
      ];

      $this->executeQueryForUpdate($sql, $parameters);
    }

  /**
   * Insert one Tronc
   *
   * @param TroncEntity $tronc The tronc to update
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function insert(TroncEntity $tronc, int $ulId):void
  {
    /** @noinspection SyntaxError */
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
      throw new InvalidArgumentException("Invalid number of tronc to be created ".$tronc->nombreTronc);
    }
    for($i=0;$i<$tronc->nombreTronc;$i++)
    {
      $sql .="(:ul_id, NOW(), :enabled, :notes, :type)".($i<$tronc->nombreTronc-1?",":"");
    }

    $parameters = [
      "ul_id"    => $ulId,
      "enabled"  => $tronc->enabled===true?"1":"0",
      "notes"    => $tronc->notes,
      "type"     => $tronc->type
    ];

    $this->executeQueryForInsert($sql, $parameters, false);
  }

  /**
   * Get the current number of Troncs for the Unite Local
   *
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the number of troncs
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function getNumberOfTroncs(int $ulId):int
  {
    $sql="
    SELECT 1
    FROM   tronc
    WHERE  ul_id = :ul_id
    ";
    $parameters = ["ul_id" => $ulId];
    return $this->getCountForSQLQuery($sql, $parameters);
  }


  /**
   * Mark All Troncs as printed
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function markAllAsPrinted(int $ulId):void
  {
    $sql = "
UPDATE `tronc`
SET    `qr_code_printed` = 1
WHERE  `ul_id`           = :ul_id
";
    $parameters["ul_id"] = $ulId;

    $this->executeQueryForUpdate($sql, $parameters);
  }
}
