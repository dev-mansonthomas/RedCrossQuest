<?php

namespace RedCrossQuest\DBService;

use \RedCrossQuest\Entity\PointQueteEntity;
use PDOException;

class PointQueteDBService extends DBService
{


  /**
   * Get all pointQuete for UL $ulId
   *
   * @param int $ulId The ID of the Unite Locale
   * @return PointQueteEntity[]  The list of PointQuete
   * @throws PDOException if the query fails to execute on the server
   */
  public function getPointQuetes(int $ulId)
  {

    $sql = "
SELECT pq.`id`,
    pq.`ul_id`,
    pq.`code`,
    pq.`name`,
    pq.`latitude`,
    pq.`longitude`,
    pq.`address`,
    pq.`postal_code`,
    pq.`city`,
    pq.`max_people`,
    pq.`advice`,
    pq.`localization`,
    pq.`minor_allowed`,
    pq.`created`,
    pq.`enabled`,
    pq.`type`,
    pq.`time_to_reach`,
    pq.`transport_to_reach`
FROM `point_quete` AS pq
WHERE pq.id > 0
AND pq.ul_id = :ul_id
AND pq.enabled = 1
ORDER BY type ASC, name ASC
";


    $stmt = $this->db->prepare($sql);
    $stmt->execute(["ul_id" => $ulId]);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = new PointQueteEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }


  /**
   * Search  pointQuete for UL $ulId
   *
   * @param  string $query query string to search across name, code, address, city
   * @param  int $point_quete_type type of point quete.
   * @param  bool $active if the point quete is active or not
   * @param  int $ulId The ID of the Unite Locale
   * @return PointQueteEntity[]  The list of PointQuete
   * @throws PDOException if the query fails to execute on the server
   */
  public function searchPointQuetes(?string $query, ?int $point_quete_type, bool $active, int $ulId)
  {


    $parameters      = ["ul_id" => $ulId, "active" => $active];
    $querySQL        = "";
    $typeSQL         = "";



    if($query !== null)
    {
      $querySQL = "
AND  
(       UPPER(pq.`name`   ) like concat('%', UPPER(:query), '%')
  OR    UPPER(pq.`code`   ) like concat('%', UPPER(:query), '%')
  OR    UPPER(pq.`address`) like concat('%', UPPER(:query), '%')
  OR    UPPER(pq.`city`   ) like concat('%', UPPER(:query), '%')
)
";
      $parameters["query"]=$query;
    }

    if($point_quete_type > 0)
    {
      $typeSQL = "
AND pq.`type` = :type
";
      $parameters["type"]=$point_quete_type;
    }


    $sql = "
SELECT pq.`id`,
    pq.`ul_id`,
    pq.`code`,
    pq.`name`,
    pq.`latitude`,
    pq.`longitude`,
    pq.`address`,
    pq.`postal_code`,
    pq.`city`,
    pq.`max_people`,
    pq.`advice`,
    pq.`localization`,
    pq.`minor_allowed`,
    pq.`created`,
    pq.`enabled`,
    pq.`type`,
    pq.`time_to_reach`,
    pq.`transport_to_reach`
FROM `point_quete` AS pq
WHERE pq.id       > 0
AND   pq.ul_id    = :ul_id
AND   pq.enabled  = :active
$querySQL
$typeSQL
ORDER by name ASC
";

    $this->logger->addError("parameters", Array("parameters"=>$parameters));
    $this->logger->addError("parameters", Array("sql"=>$sql));

    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = new PointQueteEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }




  /**
   * Get one pointQuete by its ID
   *
   * @param int $point_quete_id The ID of the point_quete
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return PointQueteEntity  The point quete info
   * @throws PDOException if the query fails to execute on the server
   */
  public function getPointQueteById(int $point_quete_id, int $ulId)
  {
    $sql = "
SELECT  pq.`id`,
        pq.`ul_id`,
        pq.`code`,
        pq.`name`,
        pq.`latitude`,
        pq.`longitude`,
        pq.`address`,
        pq.`postal_code`,
        pq.`city`,
        pq.`max_people`,
        pq.`advice`,
        pq.`localization`,
        pq.`minor_allowed`,
        pq.`created`,
        pq.`enabled`,
        pq.`type`,
        pq.`time_to_reach`,
        pq.`transport_to_reach`
FROM `point_quete` AS pq
WHERE  pq.id    = :point_quete_id
AND    pq.ul_id = :ul_id";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["point_quete_id" => $point_quete_id, "ul_id" => $ulId]);

    $point_quete = new PointQueteEntity($stmt->fetch());
    $stmt->closeCursor();
    return $point_quete;
  }


  /**
   * Update one point de quete
   *
   * @param PointQueteEntity $pointQuete The pointQuete to update
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId id of the role of the user performing the action. If != 9, limit the query to the UL of the user
   */
  public function update(PointQueteEntity $pointQuete, int $ulId, int $roleId)
  {
    $sql = "
UPDATE `point_quete`
SET
`code`              =  :code                 ,
`name`              =  :name                 ,
`latitude`          =  :latitude             ,
`longitude`         =  :longitude            ,
`address`           =  :address              ,
`postal_code`       =  :postal_code          ,
`city`              =  :city                 ,
`max_people`        =  :max_people           ,
`advice`            =  :advice               ,
`localization`      =  :localization         ,
`minor_allowed`     =  :minor_allowed        ,
`enabled`           =  :enabled              ,
`type`              =  :type                 ,
`time_to_reach`     =  :time_to_reach        ,
`transport_to_reach`=  :transport_to_reach    
WHERE `id`              = :id
";
    $parameters = [
      "code"               => $pointQuete->code               ,
      "name"               => $pointQuete->name               ,
      "latitude"           => $pointQuete->latitude           ,
      "longitude"          => $pointQuete->longitude          ,
      "address"            => $pointQuete->address            ,
      "postal_code"        => $pointQuete->postal_code        ,
      "city"               => $pointQuete->city               ,
      "max_people"         => $pointQuete->max_people         ,
      "advice"             => $pointQuete->advice             ,
      "localization"       => $pointQuete->localization       ,
      "minor_allowed"      => $pointQuete->minor_allowed      ,
      "enabled"            => $pointQuete->enabled            ,
      "type"               => $pointQuete->type               ,
      "time_to_reach"      => $pointQuete->time_to_reach      ,
      "transport_to_reach" => $pointQuete->transport_to_reach ,
      "id"                 => $pointQuete->id
    ];

    if($roleId != 9)
    {
      $sql .= "
AND   `ul_id`           = :ul_id      
";
      $parameters["ul_id"] = $ulId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    $stmt->closeCursor();
  }



  /**
   * Insert one pointQuete
   *
   * @param PointQueteEntity $pointQuete The pointQuete to update
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the primary key of the new pointQuete
   */
  public function insert(PointQueteEntity $pointQuete, int $ulId)
  {
    $sql = "
  INSERT INTO `point_quete`
  (
  `code`              ,
  `name`              ,
  `latitude`          ,
  `longitude`         ,
  `address`           ,
  `postal_code`       ,
  `city`              ,
  `max_people`        ,
  `advice`            ,
  `localization`      ,
  `minor_allowed`     ,
  `enabled`           ,
  `type`              ,
  `time_to_reach`     ,
  `transport_to_reach`,
  `ul_id`             ,
  `created`
  )
  VALUES
  (
    :code                 , 
    :name                 , 
    :latitude             , 
    :longitude            , 
    :address              , 
    :postal_code          , 
    :city                 , 
    :max_people           , 
    :advice               , 
    :localization         , 
    :minor_allowed        , 
    :enabled              , 
    :type                 , 
    :time_to_reach        , 
    :transport_to_reach   ,
    :ul_id                ,
    NOW()     
  
  )
  ";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $stmt->execute([
      "code"               => $pointQuete->code               ,
      "name"               => $pointQuete->name               ,
      "latitude"           => $pointQuete->latitude           ,
      "longitude"          => $pointQuete->longitude          ,
      "address"            => $pointQuete->address            ,
      "postal_code"        => $pointQuete->postal_code        ,
      "city"               => $pointQuete->city               ,
      "max_people"         => $pointQuete->max_people         ,
      "advice"             => $pointQuete->advice             ,
      "localization"       => $pointQuete->localization       ,
      "minor_allowed"      => $pointQuete->minor_allowed      ,
      "enabled"            => $pointQuete->enabled            ,
      "type"               => $pointQuete->type               ,
      "time_to_reach"      => $pointQuete->time_to_reach      ,
      "transport_to_reach" => $pointQuete->transport_to_reach ,
      "ul_id"              => $pointQuete->ul_id
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row  = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];

    $stmt->closeCursor();
    $this->db->commit ();

    return $lastInsertId;
  }
}
