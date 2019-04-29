<?php

namespace RedCrossQuest\DBService;

require '../../vendor/autoload.php';

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
   * @throws \Exception in other situations, possibly : parsing error in the entity
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

    $ul = new UniteLocaleEntity($stmt->fetch(), $this->logger);
    $stmt->closeCursor();

    return $ul;
  }



  /**
   * Update UL name and address
   *
   * @param UniteLocaleEntity $ul The UL info
   * @param int           $ulId  The id of the UL to be updated
   * @param int           $userId the id of the person who approve or reject the registration
   * @throws PDOException if the query fails to execute on the server
   */
  public function updateUL(UniteLocaleEntity $ul, int $ulId, int $userId)
  {

    $sql = "
UPDATE `ul`
SET
  `name`        = :name,
  `address`     = :address,
  `postal_code` = :postal_code ,
  `city`        = :city,
  `longitude`   = :longitude,
  `latitude`    = :latitude
WHERE `id`      = :id
";
    $parameters = [
      "name"        => $ul->name,
      "address"     => $ul->address,
      "postal_code" => $ul->postal_code,
      "city"        => $ul->city,
      "longitude"   => $ul->longitude,
      "latitude"    => $ul->latitude,
      "id"          => $ulId
    ];


    $stmt = $this->db->prepare($sql);
    $this->logger->warning("Updating Unite Locale id: $ulId by userId:$userId", array("parameters"=>$parameters));
    $stmt->execute($parameters);

    $stmt->closeCursor();
  }



  /**
   * Search unite locale by name, postal code, city
   *
   * @param string $query : the criteria to search queteur on first_name, last_name, nivol
   * @return UniteLocaleEntity[]  the list of UniteLocale
   * @throws PDOException if the query fails to execute on the server
   * @throws \Exception in other situations, possibly : parsing error in the entity
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
        $results[$i++] = new UniteLocaleEntity($row, $this->logger);
      }

      $stmt->closeCursor();
      return $results;
    }
  }



}
