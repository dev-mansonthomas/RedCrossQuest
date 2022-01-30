<?php

namespace RedCrossQuest\DBService;

use Exception;
use PDOException;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\Entity\PageableResponseEntity;
use RedCrossQuest\Entity\PointQueteEntity;

class PointQueteDBService extends DBService
{
  /**
   * Get all pointQuete for UL $ulId
   *
   * @param int $ulId The ID of the Unite Locale
   * @param int|null $queteurId if we want to export only the PointQuete that has been used by a queteur, we provide it's ID. (for data export)
   * @return PointQueteEntity[]  The list of PointQuete
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getPointQuetes(int $ulId,?int $queteurId=null):array
  {
    $parameters = ["ul_id" => $ulId];

    if($queteurId != null)
    {
      $restrictToQueteurSQL ="
AND pq.id       in (
    select tq.point_quete_id
    from   tronc_queteur tq
    where  tq.queteur_id = :queteur_id
)
";
      $parameters['queteur_id']=$queteurId;
    }
    else
    {
      //when exporting data fr
      $restrictToQueteurSQL ="
      AND pq.enabled = 1
";
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
WHERE pq.id > 0
AND pq.ul_id = :ul_id
$restrictToQueteurSQL
ORDER BY type, name
";


    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new PointQueteEntity($row, $this->logger);
    });
  }


  /**
   * Search  pointQuete for UL $ulId
   *
   * @param PageableRequestEntity $pageableRequest
   *         string $query query string to search across name, code, address, city
   *         int $point_quete_type type of point quete.
   *         bool $active if the point quete is active or not
   *         int $ulId The ID of the Unite Locale
   * @return PageableResponseEntity  The list of PointQuete
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function searchPointQuetes(PageableRequestEntity $pageableRequest):PageableResponseEntity
  {
//?string $query, ?int $point_quete_type, bool $active, int $ulId

    /** @var string $query */
    $query            = $pageableRequest->filterMap['q'];
    /** @var int $point_quete_type */
    $point_quete_type = $pageableRequest->filterMap['point_quete_type'];

    $parameters      = ["ul_id" => $pageableRequest->filterMap['ul_id'], "active" =>$pageableRequest->filterMap['active']];
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
ORDER BY type ASC, name ASC
";

    $count   = $this->getCountForSQLQuery ($sql, $parameters);

    $results = $this->executeQueryForArray($sql, $parameters, function($row) {
      return new PointQueteEntity($row, $this->logger);
    }, $pageableRequest->pageNumber, $pageableRequest->rowsPerPage);

    return new PageableResponseEntity($count, $results, $pageableRequest->pageNumber, $pageableRequest->rowsPerPage);

  }




  /**
   * Get one pointQuete by its ID
   *
   * @param int $point_quete_id The ID of the point_quete
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId the role of the user. If super user, can search other UL
   * @return PointQueteEntity  The point quete info
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getPointQueteById(int $point_quete_id, int $ulId, int $roleId):PointQueteEntity
  {
    $parameters = ["point_quete_id" => $point_quete_id];
    $ulRestriction="";
    if($roleId < 9)
    {
      $ulRestriction = "AND    pq.ul_id = :ul_id
LIMIT 1";

      $parameters["ul_id"]=$ulId;
    }


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
$ulRestriction
";
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new PointQueteEntity($row, $this->logger);
    }, true);
  }


  /**
   * Update one point de quete
   *
   * @param PointQueteEntity $pointQuete The pointQuete to update
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId id of the role of the user performing the action. If != 9, limit the query to the UL of the user
   * @throws Exception
   */
  public function update(PointQueteEntity $pointQuete, int $ulId, int $roleId):void
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
      "minor_allowed"      => $pointQuete->minor_allowed===true?"1":"0"  ,
      "enabled"            => $pointQuete->enabled===true?"1":"0"        ,
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
    $this->executeQueryForUpdate($sql, $parameters);
  }


  /**
   * Insert one pointQuete
   *
   * @param PointQueteEntity $pointQuete The pointQuete to update
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the primary key of the new pointQuete
   * @throws Exception
   */
  public function insert(PointQueteEntity $pointQuete, int $ulId):int
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
      "minor_allowed"      => $pointQuete->minor_allowed===true?"1":"0"  ,
      "enabled"            => $pointQuete->enabled===true?"1":"0"        ,
      "type"               => $pointQuete->type               ,
      "time_to_reach"      => $pointQuete->time_to_reach      ,
      "transport_to_reach" => $pointQuete->transport_to_reach ,
      "ul_id"              => $ulId
    ];

    return $this->executeQueryForInsert($sql, $parameters, true);
  }

  /**
   * Get the current number of point_quete for the Unite Local
   *
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the number of point_quete
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function getNumberOfPointQuete(int $ulId):int
  {
    $sql="
    SELECT count(1) as cnt
    FROM   point_quete
    WHERE  ul_id = :ul_id
    ";
    $parameters = ["ul_id" => $ulId];
    return $this->getCountForSQLQuery($sql, $parameters);
  }


  /**
   * Called only one time, at the first connection of the admin of the UL to set the first point_quete
   * @param int $ulId
   * @throws Exception
   */
  public function initBasePointQuete(int $ulId):void
  {
    $sql="
    INSERT INTO point_quete
    (ul_id, code, name, latitude, longitude, address, postal_code, city, max_people, advice, localization, minor_allowed, created, enabled, type, time_to_reach, transport_to_reach)
    SELECT id, '', concat('Base ', replace(u.name, 'UL ', '')), u.latitude, u.longitude, u.address, u.postal_code, u.city, 2, '', '', 1, NOW(), 1, 4, 0, 1
    FROM  ul u
    WHERE id = :id
    ";

    $parameters = ["id"=> $ulId];
    $this->executeQueryForInsert($sql, $parameters);
  }
}
