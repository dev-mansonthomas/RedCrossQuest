<?php

namespace RedCrossQuest\DBService;

use Exception;
use PDOException;
use Ramsey\Uuid\Uuid;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\Entity\PageableResponseEntity;
use RedCrossQuest\Entity\QueteurEntity;


class QueteurDBService extends DBService
{

  /**
   * Count the number of active queteur with no tronc_queteur since $yearsOfInactivity years for an UL and are not an active RCQ user.
   * Are counted, active queteurs that don't have a tronc_queteur with "comptage" date older than $yearsOfInactivity and that is not deleted
   * Are NOT counted : queteur that are also user of RCQ, with an active account and a password set
   *                   queteur that have been created this year
   *
   *
   * @param int $yearsOfInactivity  the number of years
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the count of inactive queteur
   * @throws Exception in other situations
   */
  public function countInactiveQueteurs(int $yearsOfInactivity, int $ulId):int
  {
    $sql = "
SELECT 1
FROM  queteur  q
WHERE q.active = 1 
AND   q.ul_id  = :ul_id
AND   EXTRACT(YEAR from q.created) != EXTRACT(YEAR from NOW())
AND   q.id    NOT IN
(
  SELECT tq.queteur_id  
  FROM   tronc_queteur tq
  WHERE  tq.queteur_id = q.id
  AND    tq.deleted    = 0
  AND    EXTRACT(YEAR from comptage) >= EXTRACT(YEAR from NOW()) - :years_of_inactivity
)
AND q.id NOT IN
(
  SELECT u.queteur_id
  FROM   users u
  WHERE  u.queteur_id = q.id
  AND    u.active     =1
  AND    length(u.password) > 0
)
";
    $parameters = ["ul_id" => $ulId, "years_of_inactivity"=>$yearsOfInactivity];
    return $this->getCountForSQLQuery($sql, $parameters);
  }


  /**
   * Disable queteur not active since $yearsOfInactivity years for an UL.
   * Are counted, active queteurs that don't have a tronc_queteur with "comptage" date older than $yearsOfInactivity and that is not deleted
   * Are NOT counted : queteur that are also user of RCQ, with an active account and a password set
   *                   queteur that have been created this year
   * 
   * @param int $yearsOfInactivity  the number of years
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the count of inactive queteur
   * @throws Exception in other situations
   */
  public function disableInactiveQueteurs(int $yearsOfInactivity, int $ulId):int
  {
    $sql = "
UPDATE  queteur q
SET   q.active = 0
WHERE q.active = 1 
AND   EXTRACT(YEAR from q.created) != EXTRACT(YEAR from NOW())
AND   q.ul_id  = :ul_id
AND   q.id    NOT IN
(
  SELECT tq.queteur_id  
  FROM   tronc_queteur tq
  WHERE  tq.queteur_id = q.id
  AND    tq.deleted    = 0
  AND    EXTRACT(YEAR from comptage) >= EXTRACT(YEAR from NOW()) - :years_of_inactivity
)
AND q.id NOT IN
(
  SELECT u.queteur_id
  FROM   users u
  WHERE  u.queteur_id = q.id
  AND    u.active     =1
  AND    length(u.password) > 0
)
";
    $parameters = ["ul_id" => $ulId, "years_of_inactivity"=>$yearsOfInactivity];
    return $this->executeQueryForUpdate($sql, $parameters);
  }

  /**
   * Count the number of pending registration
   *
   *
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the count of pending registration
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations
   */
  public function countPendingQueteurRegistration(int $ulId):int
  {

    $sql = "
SELECT 1
FROM `queteur_registration` qr, ul_settings us
where us.ul_id = :ul_id
AND 
(
  qr.ul_registration_token = us.token_benevole
  OR
  qr.ul_registration_token = us.token_benevole_1j
)
AND registration_approved is null
";
    $parameters = ["ul_id" => $ulId];
    return $this->getCountForSQLQuery($sql, $parameters);
  }

  /**
   * List the pending registration
   *
   * @param PageableRequestEntity $pageableRequest
   *        int $ulId               Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   *        int $registrationStatus 0: pending registration, 1: approved registration, 2: refused registration
   *
   * @return PageableResponseEntity the list of Queteurs
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations
   */
  public function listPendingQueteurRegistration(PageableRequestEntity $pageableRequest):PageableResponseEntity
  {
    //int $ulId, int $registrationStatus):array

    $parameters      = ["ul_id" => $pageableRequest->filterMap['ul_id']];

    /** @var int $registrationStatus */
    $registrationStatus = $pageableRequest->filterMap['registration_status'];

    $registrationStatusSQL = null;

    if ($registrationStatus == 0 || $registrationStatus == null)
    {
      $registrationStatusSQL = "AND registration_approved is null";
    }
    else if ($registrationStatus == 1)
    {
      $registrationStatusSQL = "AND registration_approved = 1";
    }
    else if ($registrationStatus == 2)
    {
      $registrationStatusSQL = "AND registration_approved = 0";
    }

    $sql = "
SELECT qr.`id`,
       qr.`first_name`,
       qr.`last_name`,
       qr.`man`,
       qr.`birthdate`,
       qr.`email`,
       qr.`secteur`,
       qr.`nivol`,
       qr.`mobile`,
       qr.`created`,
       qr.`ul_registration_token`,
       qr.`queteur_registration_token`,
       qr.`registration_approved`,
       qr.`reject_reason`,
       qr.`queteur_id`
FROM `queteur_registration` qr, ul_settings us
where us.ul_id = :ul_id
AND 
(
  qr.ul_registration_token = us.token_benevole
  OR
  qr.ul_registration_token = us.token_benevole_1j
)
$registrationStatusSQL
ORDER BY qr.created desc
";
    $count   = $this->getCountForSQLQuery ($sql, $parameters);
    $results = $this->executeQueryForArray($sql, $parameters, function($row) {
      return new QueteurEntity($row, $this->logger);
    }, $pageableRequest->pageNumber, $pageableRequest->rowsPerPage);

    return new PageableResponseEntity($count, $results, $pageableRequest->pageNumber, $pageableRequest->rowsPerPage);
  }


  /**
   * Get a pending registration.
   * This will fetch the registration only if it's a registration for the current UL and the registration have not already been approved or rejected.
   *
   * @param int $ulId           Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $registrationId the id of the registration
   *
   * @return QueteurEntity  the Queteurs Registration
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations
   */
  public function getQueteurRegistration(int $ulId, int $registrationId):QueteurEntity
  {
    $sql = "
SELECT qr.`id`,
       qr.`first_name`,
       qr.`last_name`,
       qr.`man`,
       qr.`birthdate`,
       qr.`email`,
       qr.`secteur`,
       qr.`nivol`,
       qr.`mobile`,
       qr.`created`,
       qr.`ul_registration_token`,
       qr.`queteur_registration_token`,
       qr.`registration_approved`,
       qr.`reject_reason`,
       qr.`queteur_id`,
       u.`id`        as ul_id,
       u.`name`      as ul_name,
       u.`longitude` as ul_longitude,
       u.`latitude`  as ul_latitude
FROM `queteur_registration` qr, 
            `ul_settings`   us,
            `ul`            u
where us.`ul_id` = :ul_id
AND   us.`ul_id` = u.`id`
AND 
(
  qr.`ul_registration_token` = us.`token_benevole`
  OR
  qr.`ul_registration_token` = us.`token_benevole_1j`
)
AND qr.`id` = :id
AND qr.`registration_approved` is null
";

    $parameters = ["ul_id" => $ulId, "id" => $registrationId];
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new QueteurEntity($row, $this->logger);
    }, true);
  }


  /**
   * Update one queteur registration with the approval decision, and if approved the newly inserted queteur id
   *
   * @param QueteurEntity $queteur The queteur to update (with the registration specific data)
   * @param int $queteurId The id of the newly inserted queteur
   * @param int $userId the id of the person who approve or reject the registration
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function updateQueteurRegistration(QueteurEntity $queteur, int $queteurId, int $userId)
  {
    $sql = "
UPDATE `queteur_registration`
SET
  `approval_date`           = NOW(),
  `registration_approved`   = :registration_approved,
  `reject_reason`           = :reject_reason ,
  `queteur_id`              = :queteur_id,
  `approver_user_id`        = :user_id 
WHERE `id`                  = :id
AND   ul_registration_token = :ul_registration_token
";
    $parameters = [
      "registration_approved"  => $queteur->registration_approved == true ? 1:0,
      "reject_reason"          => $queteur->reject_reason,
      "queteur_id"             => $queteurId,
      "user_id"                => $userId,
      "id"                     => $queteur->registration_id,
      "ul_registration_token"  => $queteur->ul_registration_token
    ];
    
    $this->executeQueryForUpdate($sql, $parameters);
  }

  /**
   * Update one queteur registration with as approved and associated with an existing queteur.
   *
   * @param QueteurEntity $queteur The queteur to update (with the registration specific data)
   * @param int $userId the id of the person who approve or reject the registration
   * @param int $ulId the id of the ul of the current user
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function associateRegistrationWithExistingQueteur(QueteurEntity $queteur, int $userId, int $ulId)
  {

    $sql = "
UPDATE `queteur_registration`
SET
  `approval_date`           = NOW(),
  `registration_approved`   = true,
  `reject_reason`           = '' ,
  `queteur_id`              = :queteur_id,
  `approver_user_id`        = :user_id 
WHERE `id`                  = :id
AND   ul_registration_token = :ul_registration_token
";
    $parameters = [

      "queteur_id"             => $queteur->id,
      "user_id"                => $userId,
      "id"                     => $queteur->registration_id,
      "ul_registration_token"  => $queteur->ul_registration_token
    ];

    $this->executeQueryForUpdate($sql, $parameters);


//forcing the user to active state
    $sql2 = "
UPDATE `queteur`
SET
    `active`  = true,
    `updated` = NOW()
WHERE `id`    = :id
AND   ul_id   = :ul_id
";
    $parameters2 = [
      "id"     => $queteur->id,
      "ul_id"  => $ulId
    ];

    $this->executeQueryForUpdate($sql2, $parameters2);

    $this->logger->debug("associateRegistrationWithExistingQueteur : Associating registration with existing queteur, and set this last as active", array("parameters"=>$parameters, "parameters2"=>$parameters2));

  }

  /**
   * Search queteur inside an Unite Local of id $ulId
   *
   * if $query is not null, then it will search the "$query" string is inside first_name, last_name, nivol columns
   *
   * @param string $query : the criteria to search queteur on first_name, last_name, nivol
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return QueteurEntity[]  the list of Queteurs
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations
   */
  public function getQueteurs(string $query, int $ulId)
  {

    $sql = "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`ul_id`,
        q.`notes`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`
FROM `queteur` q
WHERE q.ul_id = :ul_id
";

    $parameters = ["ul_id" => $ulId];

    if ($query !== null)
    {
      $sql .= "
AND  
(       UPPER(q.`first_name`) like concat('%', UPPER(:query), '%')
  OR    UPPER(q.`last_name` ) like concat('%', UPPER(:query ), '%')
  OR    UPPER(q.`nivol`     ) like concat('%', UPPER(:query     ), '%')
  OR    CONVERT(q.`id`, CHAR)     like concat(:query,'%')
)
";
      $parameters["query"]= $query;
    }

    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new QueteurEntity($row, $this->logger);
    });
  }


  /**
   * search all queteur according to the following criteria
   *

   *
   * @param  PageableRequestEntity $pageableRequestEntity
   * expected data :
   * string  $query search string compared against first_name, last_name, nivol using like '%query%'
   * int     $searchType
   *                             0 : All queteur, whether they did quete one time or not
   *                             1 : queteur that are registered for one tronc_quete but who didn't left yet
   *                             2 : queteur that are still on the street ( registered for one tronc_quete and have a depart date non null and a retour date null)
   * int     $secteur      secteur to search (1:Social, 2: Secours, 3:Non Bénévole, 4:Commerçant, 5: Spécial)
   * int     $ulId         Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * boolean $active       search only active or inactive queteurs
   * boolean $benevoleOnly Retourne que les bénévoles et anciens bénévoles (usecase: recherche de référent pour le queteur d'un jour)
   * boolean $rcqUser      return only RCQ users
   * string  $queteurIds   IDs of queteurs separated by comma to search
   * int     $QRSearchType Type of QRCode Search :  0 all, 1: Printed, 2: Not printed
   * bool    $rcqUserActif Recherche que les utilisateurs actifs
   *
   * @return PageableResponseEntity list of Queteurs
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations
   */

  //?string $query, ?int $searchType, ?int $secteur, ?int $ulId,
  //                                 bool $active, bool $benevoleOnly, bool $rcqUser, bool $rcqUserActif,
  //                                 ?string $queteurIds, ?int $QRSearchType
  public function searchQueteurs(PageableRequestEntity $pageableRequestEntity)
  {
    $parameters      = ["ul_id" => $pageableRequestEntity->filterMap['ul_id']];
    /** @var string $query */
    $query           = $pageableRequestEntity->filterMap['q'];
    /** @var int $searchType */
    $searchType      = $pageableRequestEntity->filterMap['searchType'];
    /** @var int $secteur */
    $secteur         = $pageableRequestEntity->filterMap['secteur'];
    /** @var bool $active */
    $active          = $pageableRequestEntity->filterMap['active'];
    /** @var bool $benevoleOnly */
    $benevoleOnly    = $pageableRequestEntity->filterMap['benevoleOnly'];
    /** @var bool $rcqUser */
    $rcqUser         = $pageableRequestEntity->filterMap['rcqUser'];
    /** @var bool $rcqUserActif */
    $rcqUserActif    = $pageableRequestEntity->filterMap['rcqUserActif'];
    /** @var string $queteurIds */
    $queteurIds      = $pageableRequestEntity->filterMap['queteurIds'];
    /** @var int $QRSearchType */
    $QRSearchType    = $pageableRequestEntity->filterMap['QRSearchType'];



    $querySQL        = "";
    $secteurSQL      = "";
    $benevoleOnlySQL = "";
    $rcqUserSQL      = "";
    $queteurIdsSQL   = "";
    $QRSearchTypeSQL = "";

    if($query !== null && trim($query) !='')
    {
      $querySQL = "
AND  
(       UPPER(q.`first_name`) like concat('%', UPPER(:query), '%')
  OR    UPPER(q.`last_name` ) like concat('%', UPPER(:query), '%')
  OR    UPPER(q.`nivol`     ) like concat('%', UPPER(:query), '%')
  OR    CONVERT(q.`id`, CHAR) like concat(           :query , '%')
)
";
      $parameters["query"]=$query;
    }

    if($secteur !== null && $secteur > 0)
    {
      $secteurSQL = "
AND q.`secteur` = :secteur
";
      $parameters["secteur"]=$secteur;
    }

    if($benevoleOnly == 1)
    {// only secours/social/ former volunteer
      $benevoleOnlySQL="
AND q.`secteur` IN (1,2,4)
";
    }

    if($rcqUser == 1)
    {
      $rcqUserSQL="
AND EXISTS (SELECT u.queteur_id from users u where u.queteur_id = q.id AND u.active = :rcqUserActif)      
";
      $parameters["rcqUserActif"]=$rcqUserActif=="1"?1:0;
    }

    if($QRSearchType > 0)
    {
      $QRSearchTypeSQL = "
AND qr_code_printed = :qr_code_printed      
      ";
      $parameters["qr_code_printed"]=$QRSearchType=="1"?1:0;
    }

    // Only for QRCode Search,
    if($queteurIds !== null && $queteurIds !== "")
    {

      if(strpos($queteurIds, ",") === false)
      {
        $queteurIdsSQL="
AND q.id = :queteurId
";
        $parameters["queteurId"]=$queteurIds;
      }
      else
      {
        $queteurIdsArray = explode(",", $queteurIds);

        $queteurIdsSQL = "
AND q.id IN (
";
        $i=0;
        foreach($queteurIdsArray as $qId)
        {
          $queteurIdsSQL.=":queteur_$i,";
          $parameters["queteur_$i"]=substr($qId,0,20);
          $i++;
        }
        $queteurIdsSQL.="-10)";//-10 trick to always have something in the where clause
      }
    }



    $sql = null;
    switch ($searchType)
    {
      case 0:
        $sql = $this->getSearchAllQuery         ($querySQL, $secteurSQL, $benevoleOnlySQL,  $rcqUserSQL, $queteurIdsSQL, $QRSearchTypeSQL);
        break;
      case 1:
        $sql = $this->getSearchNotLeftQuery     ($querySQL, $secteurSQL, $rcqUserSQL);
        break;
      case 2:
        $sql = $this->getSearchNotReturnedQuery ($querySQL, $secteurSQL, $rcqUserSQL);
        break;
      case 3: //search queteur when preparing a tronc-queteur
        $sql = $this->getSearchSimpleQuery      ($querySQL);
        break;

      default:
        $sql = $this->getSearchAllQuery         ($querySQL, $secteurSQL, $benevoleOnlySQL,  $rcqUserSQL, $queteurIdsSQL, $QRSearchTypeSQL);
    }

    $parameters["active"] = $active;

    $this->logger->debug("Querying queteurs", array_merge(["sql" => $sql, "searchType" => $searchType], $parameters));

    $count   = $this->getCountForSQLQuery ($sql, $parameters);
    $results = $this->executeQueryForArray($sql, $parameters, function($row) {
      return new QueteurEntity($row, $this->logger);
    }, $pageableRequestEntity->pageNumber, $pageableRequestEntity->rowsPerPage);



    return new PageableResponseEntity($count, $results, $pageableRequestEntity->pageNumber, $pageableRequestEntity->rowsPerPage);
  }


  private function getSearchSimpleQuery($querySQL)
  {

    return "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`ul_id`,
        q.`notes`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`
FROM  queteur     AS q
WHERE  q.ul_id = :ul_id
AND    q.active= :active
$querySQL 
ORDER BY q.last_name ASC
";
  }

  private function getSearchAllQuery($querySQL, $secteurSQL, $benevoleOnlySQL,  $rcqUserSQL, $queteurIdsSQL, $QRSearchTypeSQL)
  {

     return "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`ul_id`,
        q.`notes`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`,
       tq.`point_quete_id`,
       pq.name AS 'point_quete_name',
       tq.`depart_theorique`,       
       tq.`depart`, 
       tq.`retour`,
       u.name       as 'ul_name',
       u.latitude   as 'ul_latitude',
       u.longitude  as 'ul_longitude'
FROM  queteur     AS q LEFT JOIN tronc_queteur tq ON q.id = tq.queteur_id,
      point_quete AS pq, 
               ul AS u
WHERE  q.ul_id = :ul_id
AND    q.ul_id = u.id
AND    q.active= :active
$querySQL 
$secteurSQL 
$benevoleOnlySQL
$rcqUserSQL
$queteurIdsSQL
$QRSearchTypeSQL
AND  
(
  (
        tq.id IS NOT NULL
    AND tq.point_quete_id = pq.id
    AND tq.id = 
    ( 
        SELECT tqq.id 
        FROM  tronc_queteur tqq
        WHERE tqq.queteur_id = q.id
        ORDER BY tqq.depart_theorique DESC
        LIMIT 1
    )
  )
  OR 
  (
        tq.id IS NULL
    AND pq.id = 0
  )
)
ORDER BY q.last_name ASC
";
  }

  private function getSearchNotLeftQuery($querySQL, $secteurSQL, $rcqUserSQL)
  {
    return "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`ul_id`,
        q.`notes`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`,
       tq.`point_quete_id`,
       pq.name AS 'point_quete_name',
       tq.`point_quete_id`, 
       tq.`depart_theorique`, 
       tq.`depart`, 
       tq.`retour`,
        u.name       as 'ul_name',
        u.latitude   as 'ul_latitude',
        u.longitude  as 'ul_longitude' 
FROM       queteur AS q,
     tronc_queteur AS tq, 
                ul AS u,
       point_quete AS pq
WHERE  q.ul_id = :ul_id
AND    q.ul_id = u.id
AND    q.active= :active
$querySQL 
$secteurSQL 
$rcqUserSQL
AND     q.id      = tq.queteur_id
AND    tq.deleted = 0
AND    tq.id      = (  
      SELECT tqq.id 
      FROM  tronc_queteur tqq
      WHERE tqq.queteur_id  = q.id
      AND   tqq.depart      IS NULL
      AND   tqq.deleted     = 0
      ORDER BY depart_theorique DESC
      LIMIT 1
    )
AND  tq.point_quete_id = pq.id
ORDER BY q.last_name ASC
";
  }

  private function getSearchNotReturnedQuery($querySQL, $secteurSQL, $rcqUserSQL)
  {

    return "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`ul_id`,
        q.`notes`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`,
       tq.`point_quete_id`,
       tq.`depart_theorique`,
       tq.`depart`,
       tq.`retour`,
        u.name       as 'ul_name',
        u.latitude   as 'ul_latitude',
        u.longitude  as 'ul_longitude'
FROM       queteur AS q,
     tronc_queteur AS tq,
                ul AS u
WHERE  q.ul_id = :ul_id
AND    q.ul_id = u.id
AND    q.active= :active
$querySQL
$secteurSQL
$rcqUserSQL
AND     q.id      = tq.queteur_id
AND    tq.deleted = 0
AND    tq.id      = (
      SELECT tqq.id
      FROM  tronc_queteur tqq
      WHERE tqq.queteur_id = q.id
      AND   tqq.depart     IS NOT NULL
      AND   tqq.retour     IS     NULL
      ORDER BY tqq.depart_theorique DESC
      LIMIT 1
    )
ORDER BY q.last_name ASC
";
  }

  /**
   * Get one queteur by its ID
   * UL_ID is optional as this method is used by the login process, which don't know yet the UL_ID
   * @param int $queteur_id The ID of the queteur
   * @param int $ul_id Id of the UL that get the user
   * @return QueteurEntity  The queteur
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations
   */
  public function getQueteurById(int $queteur_id, int $ul_id=null)
  {
    $sql = "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`notes`,
        q.`ul_id`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`,
        q.`referent_volunteer`,
        q.`anonymization_token`,
        q.`anonymization_date`,
        u.`name`       as 'ul_name',
        u.`latitude`   as 'ul_latitude',
        u.`longitude`  as 'ul_longitude'
FROM  `queteur` AS q, 
          `ul`  AS u
WHERE  q.id    = :queteur_id
AND    q.ul_id = u.id
";
    $parameters = ["queteur_id" => $queteur_id];

    if($ul_id > 0)
    {
      $sql .= "
AND  u.id = :ul_id
";
      $parameters["ul_id"] = $ul_id;
    }
    $queteur = $this->executeQueryForObject($sql, $parameters, function($row) {
      return new QueteurEntity($row, $this->logger);
    }, true);

    if($queteur->referent_volunteer > 0)
    {
      $referent         = $this->getQueteurById($queteur->referent_volunteer, $ul_id);
      $queteur->referent_volunteer_entity = ["id"=>$referent->id, "first_name"=>$referent->first_name,"last_name"=>$referent->last_name,"nivol"=>$referent->nivol];
      $queteur->referent_volunteerQueteur = $referent->first_name ." " . $referent->last_name . " - " . $referent->nivol;
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $queteur;
  }


  /**
   * Get one queteur by its NIVOL
   * No UL_ID passed, as this method is used by the login process, which don't know yet the UL_ID
   * @param string $nivol The NIVOL of the queteur
   * @return QueteurEntity  The queteur
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations
   */
  public function getQueteurByNivol(string $nivol)
  {
    $sql = "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`notes`,
        q.`ul_id`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`,
        q.`referent_volunteer`,
        u.`name`       as 'ul_name',
        u.`latitude`   as 'ul_latitude',
        u.`longitude`  as 'ul_longitude'
FROM  `queteur` AS q, 
          `ul`  AS u
WHERE  UPPER(q.nivol)   = UPPER(:nivol)
AND    q.active  = 1
AND    q.ul_id   = u.id
";
    $parameters = ["nivol" => $nivol];
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new QueteurEntity($row, $this->logger);
    }, true);
  }


  /**
   * Update one queteur
   *
   * @param QueteurEntity $queteur The queteur to update
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId id of the role of the user performing the action. If != 9, limit the query to the UL of the user
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function update(QueteurEntity $queteur, int $ulId, int $roleId)
  {

    $sql = "
UPDATE `queteur`
SET
  `first_name`          = :first_name,
  `last_name`           = :last_name,
  `email`               = :email ,
  `secteur`             = :secteur,
  `nivol`               = :nivol,
  `mobile`              = :mobile,
  `updated`             = NOW(),
  `birthdate`           = :birthdate,
  `man`                 = :man,
  `active`              = :active,
  `referent_volunteer`  = :referent_volunteer,
  `anonymization_token` = null,
  `anonymization_date`  = null
  
WHERE `id`              = :id
";
    $parameters = [
      "first_name"          => $queteur->first_name,
      "last_name"           => $queteur->last_name,
      "email"               => $queteur->email,
      "secteur"             => $queteur->secteur,
      "nivol"               => ltrim($queteur->nivol, '0'),
      "mobile"              => $queteur->mobile,
      "birthdate"           => $queteur->birthdate,
      "man"                 => $queteur->man===true?"1":"0",
      "active"              => $queteur->active===true?"1":"0",
      "referent_volunteer"  => $queteur->referent_volunteer,
      "id"                  => $queteur->id
    ];

    if($roleId != 9)
    {//allow super admin to update any UL queteurs, but he cannot change the UL
      $sql .= "
AND   `ul_id`           = :ul_id      
";
      $parameters["ul_id"] = $ulId;
    }

    $this->executeQueryForUpdate($sql, $parameters);
  }


  /**
   * Insert one queteur
   *
   * @param QueteurEntity $queteur The queteur to update
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId role id of the user of RCQ that creates the queteur. If roleId is 9, superAdmin, then it allows the queteur to be created in any Unite Local of the super Admin choice
   * @return int the primary key of the new queteur
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function insert(QueteurEntity $queteur, int $ulId, int $roleId)
  {
    $sql = "
INSERT INTO `queteur`
(
  `first_name`,
  `last_name`,
  `email`,
  `secteur`,
  `nivol`,
  `mobile`,
  `created`,
  `updated`,
  `ul_id`,
  `birthdate`,
  `man`,
  `active`,
  `qr_code_printed`,
  `referent_volunteer`
)
VALUES
(
  :first_name,
  :last_name,
  :email,
  :secteur,
  :nivol,
  :mobile,
  NOW(),
  NOW(),
  :ul_id,
  :birthdate,
  :man,
  :active,
  false,
  :referent_volunteer
)
";
    $parameters = [
      "first_name"         => $queteur->first_name,
      "last_name"          => $queteur->last_name,
      "email"              => $queteur->email,
      "secteur"            => $queteur->secteur,
      "nivol"              => ltrim($queteur->nivol, '0'),
      "mobile"             => $queteur->mobile,
      "ul_id"              => $roleId == 9? $queteur->ul_id : $ulId,  //this ulId is safer, checked with JWT Token. if superAdmin, we take the ul_id value of queteur, than can be different from the ulId of the superAdmin
      "birthdate"          => $queteur->birthdate,
      "man"                => $queteur->man===true?"1":"0",
      "active"             => $queteur->active===true?"1":"0",
      "referent_volunteer" => $queteur->secteur == 3 ? $queteur->referent_volunteer : 0
    ];

    return $this->executeQueryForInsert($sql, $parameters, true);
  }


  /**
   * Get the current number of Queteurs for the Unite Local
   *
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the number of queteur
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function getNumberOfQueteur(int $ulId)
  {
    $sql="
    SELECT 1
    FROM   queteur
    WHERE  ul_id = :ul_id
    ";
    $parameters = ["ul_id" => $ulId];
    return $this->getCountForSQLQuery($sql, $parameters);
  }


  /***
   * Search for similar queteur as the user is currently typing while creating a new queteur
   * @param int $ulId id of the unite locale of the connected user
   * @param string $firstName what's being typed in first_name field
   * @param string $lastName what's being typed in last_name field
   * @param string $nivol what's being typed in nivol field
   * @throws PDOException if the query fails to execute on the server
   * @return QueteurEntity[]  the list of Queteurs matching the query
   * @throws Exception in other situations
   */
  public function searchSimilarQueteur(int $ulId, ?string $firstName, ?string $lastName, ?string $nivol)
  {
    $parameters      = ["ul_id" => $ulId];
    $searchFirstName = "";
    $searchLastName  = "";
    $searchNivol     = "";
    $numberOfParameters = 0;

    if($firstName != null)
    {
      $searchFirstName = "
UPPER(q.`first_name`) like UPPER(:first_name)
";
      $parameters["first_name"] = "%".$firstName."%";
      $numberOfParameters++;
    }

    if($lastName != null)
    {
      if($numberOfParameters>0)
      {
        $OR="OR";
      }
      else
      {
        $OR="";
      }

      $searchLastName = "
$OR UPPER(q.`last_name`) like UPPER(:last_name)
";
      $parameters["last_name"] = "%".$lastName."%";
      $numberOfParameters++;
    }

    if($nivol != null)
    {
      if($numberOfParameters>0)
      {
        $OR="OR";
      }
      else
      {
        $OR="";
      }

      $searchNivol = "
$OR UPPER(q.`nivol`) like UPPER(:nivol)
";
      $parameters["nivol"] = "%".$nivol."%";
    }

    $sql="    
SELECT  q.`id`,
        q.`first_name`,
        q.`last_name`,
        q.`email`,
        q.`mobile`,
        q.`nivol`,
        q.active
FROM  `queteur` AS q
WHERE  q.ul_id    = :ul_id
AND (
$searchFirstName
$searchLastName
$searchNivol
)
";
    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new QueteurEntity($row, $this->logger);
    });
  }

  /**
   * Anonymize the data of a queteur
   *
   * @param int $queteurId The queteur to update
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId id of the role of the user performing the action. If != 9, limit the query to the UL of the user
   * @param int $userId id of the user performing the action.
   * @return string token associated with the queteur
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations
   */
  public function anonymize(int $queteurId, int $ulId, int $roleId, int $userId)
  {
    $token = Uuid::uuid4();

    $sql = "
UPDATE `queteur`
SET
  `first_name`           = 'Anonimisé',
  `last_name`            = 'Quêteur',
  `email`                = '' ,
  `secteur`              = 0,
  `nivol`                = '',
  `mobile`               = '',
  `updated`              = NOW(),
  `notes`                = '',
  `birthdate`            = '1922-12-22',
  `man`                  = 0,
  `active`               = 0,
  `anonymization_token`  = :token,
  `anonymization_date`   = NOW(),
  `anonymization_user_id`= :user_id
WHERE `id`               = :id
";
    $parameters = [
      "id"        => $queteurId,
      "token"     => $token,
      "user_id"   => $userId
    ];

    if($roleId != 9)
    {//allow super admin to update any UL queteurs, but he cannot change the UL
      $sql .= "
AND   `ul_id`           = :ul_id      
";
      $parameters["ul_id"] = $ulId;
    }

    $this->executeQueryForUpdate($sql, $parameters);

    return $token;
  }


  /**
   * Mark All Queteur as printed
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function markAllAsPrinted(int $ulId)
  {

    $sql = "
UPDATE `queteur`
SET    `qr_code_printed` = 1
WHERE  `ul_id`           = :ul_id
";
    $parameters["ul_id"] = $ulId;

    $this->executeQueryForUpdate($sql, $parameters);
  }


  /**
   * Get one queteur by anonymization_token, used from the queteur_list page
   * @param string $anonymization_token The anonymisation token
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId id of the role of the user performing the action. If != 9, limit the query to the UL of the user
   * @return PageableResponseEntity  One Queteur or 0, in an array for compatibility with the search feature
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getQueteurByAnonymizationToken(string $anonymization_token, int $ulId, int $roleId):PageableResponseEntity
  {
    $parameters = ["anonymization_token" => $anonymization_token];

    $sql = "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`secteur`,
        q.`nivol`,
        q.`mobile`,
        q.`created`,
        q.`updated`,
        q.`notes`,
        q.`ul_id`,
        q.`active`,
        q.`man`,
        q.`birthdate`,
        q.`qr_code_printed`,
        q.`referent_volunteer`,
        u.`name`       as 'ul_name',
        u.`latitude`   as 'ul_latitude',
        u.`longitude`  as 'ul_longitude'
FROM  `queteur` AS q, 
          `ul`  AS u
WHERE  UPPER(q.`anonymization_token`)   = UPPER(:anonymization_token)
AND    q.`ul_id`   = u.id
";


    if($roleId != 9)
    {//allow super admin to update any UL queteurs, but he cannot change the UL
      $sql .= "
AND    q.ul_id   = :ul_id      
";
      $parameters["ul_id"] = $ulId;
    }
    
    $queteur = $this->executeQueryForObject($sql, $parameters, function($row) {
      return new QueteurEntity($row, $this->logger);
    }, false);

    return new PageableResponseEntity($queteur!=null?1:0, [$queteur],1,1);
  }
}
