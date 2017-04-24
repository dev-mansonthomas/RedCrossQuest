<?php

namespace RedCrossQuest\DBService;

use \RedCrossQuest\Entity\UniteLocaleEntity;

class UniteLocaleDBService extends DBService
{

  /**
   * Get one UniteLocale by its ID
   *
   * @param int $ulId The ID of the uniteLocale
   * @return UniteLocaleEntity  The Unite Locale
   */
  public function getPointQueteById($ulId)
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
        `ul`.`date_demarrage_rcq`
FROM    `ul`
WHERE   `ul`.id    = :ul_id
";

    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute(["ul_id" => $ulId]);

    if ($result) {
      $ul = new UniteLocaleEntity($stmt->fetch());
      $stmt->closeCursor();
      return $ul;
    }
  }
}
