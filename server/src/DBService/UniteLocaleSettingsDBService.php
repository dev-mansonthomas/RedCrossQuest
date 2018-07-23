<?php

namespace RedCrossQuest\DBService;

use PDOException;
use RedCrossQuest\Entity\UniteLocaleSettingsEntity;

class UniteLocaleSettingsDBService extends DBService
{

  /**
   * Get one UniteLocale Settings by its ID
   *
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return UniteLocaleSettingsEntity  The Unite Locale Settings
   * @throws PDOException if the query fails to execute on the server
   */
  public function getUniteLocaleById(int $ulId)
  {
    $sql = "
SELECT  `uls`.`id`,
        `uls`.`ul_id`,
        `uls`.`settings`,
        `uls`.`created`,
        `uls`.`updated`,
        `uls`.`last_update_user_id`
FROM    `ul_settings`
WHERE   `ul`.ul_id    = :ul_id
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["ul_id" => $ulId]);
    $uls = new UniteLocaleSettingsEntity($stmt->fetch());
    $stmt->closeCursor();

    return $uls;
  }



}
