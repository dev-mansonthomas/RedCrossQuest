<?php

namespace RedCrossQuest\DBService;

require '../../vendor/autoload.php';

use PDOException;
use RedCrossQuest\Entity\UniteLocaleEntity;

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
        `ul`.`publicDashboard`,
        `ul`.`president_man`,
        `ul`.`president_nivol`,
        `ul`.`president_first_name`,
        `ul`.`president_last_name`,
        `ul`.`president_email`,
        `ul`.`president_mobile`,
        `ul`.`tresorier_man`,
        `ul`.`tresorier_nivol`,
        `ul`.`tresorier_first_name`,
        `ul`.`tresorier_last_name`,
        `ul`.`tresorier_email`,
        `ul`.`tresorier_mobile`,
        `ul`.`admin_man`,
        `ul`.`admin_nivol`,
        `ul`.`admin_first_name`,
        `ul`.`admin_last_name`,
        `ul`.`admin_email`,
        `ul`.`admin_mobile`
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
  `email`                = :email,
  `name`                 = :name,
  `phone`                = :phone,
  `address`              = :address,
  `postal_code`          = :postal_code ,
  `city`                 = :city,
  `longitude`            = :longitude,
  `latitude`             = :latitude,
  `president_man`        = :president_man,       
  `president_nivol`      = :president_nivol,     
  `president_first_name` = :president_first_name,
  `president_last_name`  = :president_last_name, 
  `president_email`      = :president_email,     
  `president_mobile`     = :president_mobile,    
  `tresorier_man`        = :tresorier_man,       
  `tresorier_nivol`      = :tresorier_nivol,     
  `tresorier_first_name` = :tresorier_first_name,
  `tresorier_last_name`  = :tresorier_last_name, 
  `tresorier_email`      = :tresorier_email,     
  `tresorier_mobile`     = :tresorier_mobile,    
  `admin_man`            = :admin_man,           
  `admin_nivol`          = :admin_nivol,         
  `admin_first_name`     = :admin_first_name,    
  `admin_last_name`      = :admin_last_name,     
  `admin_email`          = :admin_email,         
  `admin_mobile`         = :admin_mobile       
WHERE `id`      = :id
";
    $parameters = [
      "email"                => $ul->email,
      "name"                 => $ul->name,
      "phone"                => $ul->phone,
      "address"              => $ul->address,
      "postal_code"          => $ul->postal_code,
      "city"                 => $ul->city,
      "longitude"            => $ul->longitude,
      "latitude"             => $ul->latitude,
      "president_man"        => $ul->president_man == 1 ?  1 : 0,
      "president_nivol"      => $ul->president_nivol,    
      "president_first_name" => $ul->president_first_name,
      "president_last_name"  => $ul->president_last_name,
      "president_email"      => $ul->president_email,    
      "president_mobile"     => $ul->president_mobile,   
      "tresorier_man"        => $ul->tresorier_man == 1 ?  1 : 0,
      "tresorier_nivol"      => $ul->tresorier_nivol,    
      "tresorier_first_name" => $ul->tresorier_first_name,
      "tresorier_last_name"  => $ul->tresorier_last_name,
      "tresorier_email"      => $ul->tresorier_email,    
      "tresorier_mobile"     => $ul->tresorier_mobile,   
      "admin_man"            => $ul->admin_man == 1 ?  1 : 0,
      "admin_nivol"          => $ul->admin_nivol,        
      "admin_first_name"     => $ul->admin_first_name,   
      "admin_last_name"      => $ul->admin_last_name,    
      "admin_email"          => $ul->admin_email,        
      "admin_mobile"         => $ul->admin_mobile,                
      "id"                   => $ulId
    ];


    $stmt = $this->db->prepare($sql);
    $this->logger->warning("Updating Unite Locale id: $ulId by userId:$userId", array("parameters"=>$parameters));
    $stmt->execute($parameters);

    $stmt->closeCursor();
  }



  /**
   * Search unite locale by name, postal code, city
   *
   * @param string $query : the criteria to search UL on name postal code city and president, tresorier, admin first & last name
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
WHERE   UPPER(ul.`name`                ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`postal_code`         ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`city`                ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`president_first_name`) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`president_last_name` ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`admin_first_name`    ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`admin_last_name`     ) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`tresorier_first_name`) like concat('%', UPPER(:query), '%')
  OR    UPPER(ul.`tresorier_last_name` ) like concat('%', UPPER(:query), '%')


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
