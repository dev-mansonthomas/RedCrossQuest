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
  public function getPointQueteById(int $ulId)
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
}
