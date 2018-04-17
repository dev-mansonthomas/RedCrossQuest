<?php

namespace RedCrossQuest\DBService;

use RedCrossQuest\Entity\QueteurEntity;
use PDOException;


class QueteurDBService extends DBService
{

  /**
   * Search queteur inside an Unite Local of id $ulId
   *
   * if $query is not null, then it will search the "$query" string is inside first_name, last_name, nivol columns
   *
   * @param string $query : the criteria to search queteur on first_name, last_name, nivol
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return QueteurEntity[]  the list of Queteurs
   * @throws PDOException if the query fails to execute on the server
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
    if ($query !== null) {
      $sql .= "
AND  
(       UPPER(q.`first_name`) like concat('%', UPPER(:first_name), '%')
  OR    UPPER(q.`last_name` ) like concat('%', UPPER(:last_name ), '%')
  OR    UPPER(q.`nivol`     ) like concat('%', UPPER(:nivol     ), '%')
)
";
    }

    $stmt = $this->db->prepare($sql);
    if ($query !== null)
    {
      $stmt->execute([
        "first_name" => $query,
        "last_name" => $query,
        "nivol" => $query,
        "ul_id" => $ulId
      ]);
    }
    else
    {
      $stmt->execute(["ul_id" => $ulId]);
    }

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = new QueteurEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }


  /**
   * search all queteur according to the following criteria
   *
   * @param string  $query search string compared against first_name, last_name, nivol using like '%query%'
   * @param int     $searchType
   *                             0 : All queteur, whether they did quete one time or not
   *                             1 : queteur that are registered for one tronc_quete but who didn't left yet
   *                             2 : queteur that are still on the street ( registered for one tronc_quete and have a depart date non null and a retour date null)
   * @param int     $secteur      secteur to search (1:Social, 2: Secours, 3:Non Bénévole, 4:Commerçant, 5: Spécial)
   * @param int     $ulId         Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param boolean $active       search only active or inactive queteurs
   * @param boolean $benevoleOnly Retourne que les bénévoles et anciens bénévoles (usecase: recherche de référent pour le queteur d'un jour)
   * @param boolean $rcqUser      return only RCQ users
   * @return QueteurEntity[] list of Queteurs
   * @throws PDOException if the query fails to execute on the server
   *
   */
  public function searchQueteurs(?string $query, ?int $searchType, ?int $secteur, ?int $ulId, bool $active, bool $benevoleOnly, bool $rcqUser)
  {
    $parameters      = ["ul_id" => $ulId];
    $querySQL        = "";
    $secteurSQL      = "";
    $benevoleOnlySQL = "";
    $rcqUserSQL      = "";


    if($query !== null)
    {
      $querySQL = "
AND  
(       UPPER(q.`first_name`) like concat('%', UPPER(:query), '%')
  OR    UPPER(q.`last_name` ) like concat('%', UPPER(:query), '%')
  OR    UPPER(q.`nivol`     ) like concat('%', UPPER(:query), '%')
)
";
      $parameters["query"]=$query;
    }

    if($secteur !== null)
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
AND EXISTS (SELECT queteur_id from users where queteur_id = q.id)      
";
    }



    $sqlSearchAll = "
SELECT 	q.`id`,
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
FROM 	queteur     AS q LEFT JOIN tronc_queteur tq ON q.id = tq.queteur_id,
		  point_quete AS pq, 
		           ul AS u
WHERE  q.ul_id = :ul_id
AND    q.ul_id = u.id
AND    q.active= :active
$querySQL 
$secteurSQL 
$benevoleOnlySQL
$rcqUserSQL
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
        ORDER BY depart_theorique DESC
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

    $sqlSearchNotLeft = "
SELECT 	q.`id`,
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
FROM 	     queteur AS q,
		 tronc_queteur AS tq, 
		            ul AS u
WHERE  q.ul_id = :ul_id
AND    q.ul_id = u.id
AND    q.active= :active
$querySQL 
$secteurSQL 
$rcqUserSQL
AND     q.id = tq.queteur_id
AND    tq.id = (	
			SELECT tqq.id 
			FROM  tronc_queteur tqq
      WHERE tqq.queteur_id = q.id
      AND   depart IS NULL
      ORDER BY depart_theorique DESC
      LIMIT 1
		)
ORDER BY q.last_name ASC
";

    $sqlSearchNotReturned = "
SELECT 	q.`id`,
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
FROM 	     queteur AS q,
		 tronc_queteur AS tq, 
		            ul AS u
WHERE  q.ul_id = :ul_id
AND    q.ul_id = u.id
AND    q.active= :active
$querySQL 
$secteurSQL 
$rcqUserSQL
AND     q.id = tq.queteur_id
AND    tq.id = (	
			SELECT tqq.id 
			FROM  tronc_queteur tqq
      WHERE tqq.queteur_id = q.id
      AND   depart IS NOT NULL
      AND   retour IS     NULL
      ORDER BY depart_theorique DESC
      LIMIT 1
		)
ORDER BY q.last_name ASC
";
    $sql = null;
    switch ($searchType) {
      case 0:
        $sql = $sqlSearchAll;
        break;
      case 1:
        $sql = $sqlSearchNotLeft;
        break;
      case 2:
        $sql = $sqlSearchNotReturned;
        break;
      default:
        $sql = $sqlSearchAll;
    }


    //$this->logger->addInfo("SQL Query for queteur search", array("sql"=>$sql));
    $stmt   = $this->db->prepare($sql);
    $parameters["active"] = $active;

    $stmt->execute($parameters);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = new QueteurEntity($row);
    }
    //$this->logger->addDebug("retrieved $i queteurs, searchType:'$searchType', secteur='$secteur', query='$query' ".print_r($parameters, true));
    $stmt->closeCursor();
    return $results;
  }

  /**
   * Get one queteur by its ID
   * No UL_ID passed, as this method is used by the login process, which don't know yet the UL_ID
   * @param int $queteur_id The ID of the queteur
   * @return QueteurEntity  The queteur
   * @throws PDOException if the query fails to execute on the server
   */
  public function getQueteurById(int $queteur_id)
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
WHERE  q.id    = :queteur_id
AND    q.ul_id = u.id
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["queteur_id" => $queteur_id]);

    $queteur = new QueteurEntity($stmt->fetch());
    $stmt->closeCursor();

    if($queteur->referent_volunteer > 0)
    {
      $referent         = $this->getQueteurById($queteur->referent_volunteer);
      $queteur->referent_volunteer_entity = ["id"=>$referent->id, "first_name"=>$referent->first_name,"last_name"=>$referent->last_name,"nivol"=>$referent->nivol];
      $queteur->referent_volunteerQueteur = $referent->first_name ." " . $referent->last_name . " - " . $referent->nivol;
    }

    return $queteur;

  }


  /**
   * Get one queteur by its NIVOL
   * No UL_ID passed, as this method is used by the login process, which don't know yet the UL_ID
   * @param string $nivol The NIVOL of the queteur
   * @return QueteurEntity  The queteur
   * @throws PDOException if the query fails to execute on the server
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

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["nivol" => $nivol]);

    $queteur = new QueteurEntity($stmt->fetch());
    $stmt->closeCursor();
    return $queteur;
  }


  /**
   * Update one queteur
   *
   * @param QueteurEntity $queteur The queteur to update
   * @param int           $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int           $roleId id of the role of the user performing the action. If != 9, limit the query to the UL of the user
   * @throws PDOException if the query fails to execute on the server
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
  `notes`               = :notes,
  `birthdate`           = :birthdate,
  `man`                 = :man,
  `active`              = :active,
  `referent_volunteer`  = :referent_volunteer
WHERE `id`              = :id
";
    $parameters = [
      "first_name"          => $queteur->first_name,
      "last_name"           => $queteur->last_name,
      "email"               => $queteur->email,
      "secteur"             => $queteur->secteur,
      "nivol"               => ltrim($queteur->nivol, '0'),
      "mobile"              => $queteur->mobile,
      "notes"               => $queteur->notes,
      "birthdate"           => $queteur->birthdate,
      "man"                 => $queteur->man,
      "active"              => $queteur->active,
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

    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    $stmt->closeCursor();
  }


  /**
   * Insert one queteur
   *
   * @param QueteurEntity $queteur  The queteur to update
   * @param int           $ulId     Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int           $roleId   role id of the user of RCQ that creates the queteur. If roleId is 9, superAdmin, then it allows the queteur to be created in any Unite Local of the super Admin choice
   * @return int the primary key of the new queteur
   * @throws PDOException if the query fails to execute on the server
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
  `notes`,
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
  :notes,
  :ul_id,
  :birthdate,
  :man,
  :active,
  false,
  :referent_volunteer
)
";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $stmt->execute([
      "first_name"         => $queteur->first_name,
      "last_name"          => $queteur->last_name,
      "email"              => $queteur->email,
      "secteur"            => $queteur->secteur,
      "nivol"              => ltrim($queteur->nivol, '0'),
      "mobile"             => $queteur->mobile,
      "notes"              => $queteur->notes,
      "ul_id"              => $roleId == 9? $queteur->ul_id : $ulId,  //this ulId is safer, checked with JWT Token. if superAdmin, we take the ul_id value of queteur, than can be different from the ulId of the superAdmin
      "birthdate"          => $queteur->birthdate,
      "man"                => $queteur->man,
      "active"             => $queteur->active,
      "referent_volunteer" => $queteur->secteur == 3 ? $queteur->referent_volunteer : 0
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];

    $stmt->closeCursor();
    $this->db->commit();
    return $lastInsertId;
  }


  /**
   * Get the current number of Queteurs for the Unite Local
   *
   * @param int           $ulId     Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the number of queteur
   * @throws PDOException if the query fails to execute on the server
   */
  public function getNumberOfQueteur(int $ulId)
  {
    $sql="
    SELECT count(1) as cnt
    FROM   queteur
    WHERE  ul_id = :ul_id
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(["ul_id" => $ulId]);
    $row = $stmt->fetch();
    return $row['cnt'];
  }


  /***
   * Search for similar queteur as the user is currently typing while creating a new queteur
   * @param int $ulId id of the unite locale of the connected user
   * @param string $firstName what's beeing typed in first_name field
   * @param string $lastName what's beeing typed in last_name field
   * @param string $nivol what's beeing typed in nivol field
   * @throws PDOException if the query fails to execute on the server
   * @return QueteurEntity[]  the list of Queteurs matching the query
   */
  public function searchSimilarQueteur(int $ulId, ?string $firstName, ?string $lastName, ?string $nivol)
  {


    $parameters      = ["ul_id" => $ulId];
    $searchFirstName = "";
    $searchLastName  = "";
    $searchNivol     = "";
    $AND             = "";
    $numberOfParameters = 0;

    if($firstName != null)
    {
      $searchFirstName = "
q.`first_name` like :first_name
";
      $parameters["first_name"] = "%".$firstName."%";
      $numberOfParameters++;
    }

    if($lastName != null)
    {
      if($numberOfParameters>0)
      {
        $AND="OR";
      }

      $searchLastName = "
$AND q.`last_name` like :last_name
";
      $parameters["last_name"] = "%".$lastName."%";
      $numberOfParameters++;
      $AND="";
    }

    if($nivol != null)
    {
      if($numberOfParameters>0)
      {
        $AND="OR";
      }

      $searchNivol = "
$AND q.`nivol` like :nivol
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

    $stmt   = $this->db->prepare($sql);
    $this->logger->addInfo($sql);
    $this->logger->addInfo(print_r($parameters, true));
    $stmt->execute($parameters);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = new QueteurEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }
}
