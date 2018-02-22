<?php

namespace RedCrossQuest\DBService;

use \RedCrossQuest\Entity\UniteLocaleEntity;
use PDOException;

class UniteLocaleDBService extends DBService
{

  /**
   * Get one UniteLocale by its ID
   *
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return UniteLocaleEntity  The Unite Locale
   * @throws PDOException if the query fails to execute on the server
   */
  public function getUniteLocaleById(int $ulId)
  {
    $sql = "
SELECT  `ul`.`id`,
        `ul`.`name`,
        `ul`.`phone`,
        `ul`.`latitude`,
        `ul`.`longitude`,
        `ul`.`address`,
        `ul`.`postal_code`,
        `ul`.`city`,
        `ul`.`external_id`,
        `ul`.`email`,
        `ul`.`id_structure_rattachement`,
        `ul`.`date_demarrage_activite`,
        `ul`.`date_demarrage_rcq`,
        `ul`.`mode`,
        `ul`.`publicDashboard`
FROM    `ul`
WHERE   `ul`.id    = :ul_id
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["ul_id" => $ulId]);

    $ul = new UniteLocaleEntity($stmt->fetch());
    $stmt->closeCursor();

    return $ul;
  }



  /**
   * Search unite locale by name, postal code, city
   *
   * @param string $query : the criteria to search queteur on first_name, last_name, nivol
   * @return UniteLocaleEntity[]  the list of UniteLocale
   * @throws PDOException if the query fails to execute on the server
   */
  public function searchUniteLocale(string $query)
  {

    $sql = "
SELECT  `ul`.`id`,
        `ul`.`name`,
        `ul`.`phone`,
        `ul`.`latitude`,
        `ul`.`longitude`,
        `ul`.`address`,
        `ul`.`postal_code`,
        `ul`.`city`,
        `ul`.`external_id`,
        `ul`.`email`,
        `ul`.`id_structure_rattachement`,
        `ul`.`date_demarrage_activite`,
        `ul`.`date_demarrage_rcq`,
        `ul`.`mode`,
        `ul`.`publicDashboard`
FROM    `ul`
WHERE   UPPER(ul.`name`        ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`postal_code` ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`city`        ) like concat('%', UPPER(:query), '%')

";



    $stmt = $this->db->prepare($sql);
    if ($query !== null)
    {
      $stmt->execute([
        "query" => $query
      ]);

      $results = [];
      $i = 0;
      while ($row = $stmt->fetch())
      {
        $results[$i++] = new UniteLocaleEntity($row);
      }

      $stmt->closeCursor();
      return $results;
    }
  }



}
