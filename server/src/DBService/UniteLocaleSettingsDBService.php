<?php

namespace RedCrossQuest\DBService;

require '../../vendor/autoload.php';

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
   * @throws \Exception in other situations, possibly : parsing error in the entity
   */
  public function getUniteLocaleById(int $ulId)
  {
    $sql = "
SELECT  `id`,
        `ul_id`,
        `settings`,
        `created`,
        `updated`,
        `last_update_user_id`,
        `token_benevole`,
        `token_benevole_1j`
FROM    `ul_settings` 
WHERE   `ul_id` = :ul_id
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["ul_id" => $ulId]);
    $uls = new UniteLocaleSettingsEntity($stmt->fetch(), $this->logger);
    $stmt->closeCursor();

    return $uls;
  }



}
