<?php

namespace RedCrossQuest\DBService;

use RedCrossQuest\Entity\QueteurEntity;


class QueteurDBService extends DBService
{

  /**
   * Search queteur inside an Unite Local of id $ulId
   *
   * if $query is not null, then it will search the "$query" string is inside first_name, last_name, nivol columns
   *
   * @param String $query : the criteria to search queteur on first_name, last_name, nivol
   * @param int    $ulId  : the ID of the UniteLocal on which the search is limited
   * @return PointQueteEntity  The PointQuete
   */
  public function getQueteurs($query, $ulId)
  {

    $sql = "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`minor`,
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
    if ($query !== null) {
      $result = $stmt->execute([
        "first_name" => $query,
        "last_name" => $query,
        "nivol" => $query,
        "ul_id" => $ulId
      ]);
    } else {
      $result = $stmt->execute(["ul_id" => $ulId]);
    }

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch()) {
      $results[$i++] = new QueteurEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }


  /**
   * search all queteur according to a query type
   * @param int $searchType
   * 0 : All queteur, whether they did quete one time or not
   * 1 : queteur that are registered for one tronc_quete but who didn't left yet
   * 2 : queteur that are still on the street ( registered for one tronc_quete and have a depart date non null and a retour date null)
   * @param int    $ulId  : the ID of the UniteLocal on which the search is limited
   * @return list of QueteurEntity
   *
   */
  public function getQueteursBySearchType($searchType, $ulId)
  {

    $sqlSearchAll = "
SELECT 	q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`minor`,
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
       tq.`retour` 
FROM 	queteur AS q LEFT JOIN tronc_queteur tq ON q.id = tq.queteur_id,
		point_quete AS pq
WHERE  
(
       q.ul_id = :ul_id
  AND tq.id IS NOT NULL
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
ORDER BY q.last_name ASC
";

    $sqlSearchNotLeft = "
SELECT 	q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`minor`,
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
       tq.`retour` 
FROM 	     queteur AS q,
		 tronc_queteur AS tq
WHERE   q.ul_id = :ul_id
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
        q.`minor`,
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
       tq.`retour` 
FROM 	     queteur AS q,
		 tronc_queteur AS tq
WHERE   q.ul_id = :ul_id
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
      case "0":
        $sql = $sqlSearchAll;
        break;
      case "1":
        $sql = $sqlSearchNotLeft;
        break;
      case "2":
        $sql = $sqlSearchNotReturned;
        break;
      default:
        $sql = $sqlSearchAll;
    }

    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute(["ul_id" => $ulId]);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch()) {
      $results[$i++] = new QueteurEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }

  /**
   * Get one queteur by its ID
   * No UL_ID passed, as this method is used by the login process, which don't know yet the UL_ID
   * @param int $queteur_id The ID of the queteur
   * @return QueteurEntity  The queteur
   */
  public function getQueteurById($queteur_id)
  {
    $sql = "
SELECT  q.`id`,
        q.`email`,
        q.`first_name`,
        q.`last_name`,
        q.`minor`,
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
        q.`referent_volunteer`
FROM  `queteur` q
WHERE  q.id   = :queteur_id
";

    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute(["queteur_id" => $queteur_id]);

    if ($result) {
      $queteur = new QueteurEntity($stmt->fetch());
      $stmt->closeCursor();
      return $queteur;
    }
  }


  /**
   * Update one queteur
   *
   * @param QueteurEntity $queteur The queteur to update
   * @param int $ulId  : the ID of the UniteLocal on which the search is limited
   */
  public function update(QueteurEntity $queteur, $ulId)
  {
    $sql = "
UPDATE `queteur`
SET
  `first_name`  = :first_name,
  `last_name`   = :last_name,
  `email`       = :email ,
  `secteur`     = :secteur,
  `nivol`       = :nivol,
  `mobile`      = :mobile,
  `updated`     = NOW(),
  `notes`       = :notes,
  `ul_id`       = :ul_id,
  `minor`       = :minor
WHERE `id`    = :id
AND   `ul_id` = :ul_id
";

    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute([
      "first_name"  => $queteur->first_name,
      "last_name"   => $queteur->last_name,
      "email"       => $queteur->email,
      "secteur"     => $queteur->secteur,
      "nivol"       => $queteur->nivol,
      "mobile"      => $queteur->mobile,
      "notes"       => $queteur->notes,
      "minor"       => $queteur->minor,
      "ul_id"       => $ulId
    ]);

    $this->logger->warning($stmt->rowCount());
    $stmt->closeCursor();

    if (!$result)
    {
      throw new Exception("could not save record ".print_r($queteur, true));
    }
  }


  /**
   * Insert one queteur
   *
   * @param QueteurEntity $queteur The queteur to update
   * @param int $ulId  : the ID of the UniteLocal on which the search is limited
   * @return int the primary key of the new queteur
   */
  public function insert(QueteurEntity $queteur, $ulId)
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
  :qr_code_printed,
  :referent_volunteer
)
";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $result = $stmt->execute([
      "first_name"         => $queteur->first_name,
      "last_name"          => $queteur->last_name,
      "email"              => $queteur->email,
      "secteur"            => $queteur->secteur,
      "nivol"              => $queteur->nivol,
      "mobile"             => $queteur->mobile,
      "notes"              => $queteur->notes,
      "ul_id"              => $ulId,  //this ulId is safer, checked with JWT Token
      "birthdate"          => $queteur->birthdate,
      "man"                => $queteur->man,
      "active"             => $queteur->active,
      "qr_code_printed"    => $queteur->qr_code_printed,
      "referent_volunteer" => $queteur->referent_volunteer
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];
    $this->logger->info('$lastInsertId:', [$lastInsertId]);

    $stmt->closeCursor();
    $this->db->commit();
    return $lastInsertId;
  }
}
