<?php
namespace RedCrossQuest;

class QueteurMapper extends Mapper
{
    public function getQueteurs($query)
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
";
      if($query!==null)
      {
        $sql .= "
WHERE UPPER(q.`first_name`) like concat('%', UPPER(:first_name), '%')
OR    UPPER(q.`last_name` ) like concat('%', UPPER(:last_name ), '%')
OR    UPPER(q.`nivol`     ) like concat('%', UPPER(:nivol     ), '%')
";
      }

      $stmt = $this->db->prepare($sql);
      if($query!==null)
      {
        $result = $stmt->execute([
          "first_name" => $query,
          "last_name"  => $query,
          "nivol"      => $query
        ]);
      }
      else
      {
        $result = $stmt->execute([]);
      }

      $results = [];
      $i=0;
      while($row = $stmt->fetch())
      {
        $results[$i++] =  new QueteurEntity($row);
      }

      $stmt->closeCursor();
      return $results;
    }


/**
 * search all queteur acording to a query type
 *
 * 0 : All queteur, whether they did quete one time or not
 * 1 : queteur that are registered for one tronc_quete but who didn't left yet
 * 2 : queteur that are still on the street ( registered for one tronc_quete and have a depart date non null and a retour date null)
 */
  public function getQueteursBySearchType($searchType)
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
       pq.name as 'point_quete_name',
       tq.`depart_theorique`,       
       tq.`depart`, 
       tq.`retour` 
FROM 	queteur AS q LEFT JOIN tronc_queteur tq ON q.id = tq.queteur_id,
		point_quete AS pq
WHERE  
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
  and pq.id = 0
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
WHERE 	q.id = tq.queteur_id
AND    tq.id = (	
			SELECT tqq.id 
			FROM  tronc_queteur tqq
      WHERE tqq.queteur_id = q.id
      AND   depart is null
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
FROM 	     queteur as q,
		 tronc_queteur as tq
WHERE 	q.id = tq.queteur_id
AND    tq.id = (	
			SELECT tqq.id 
			FROM  tronc_queteur tqq
      WHERE tqq.queteur_id = q.id
      AND   depart is not null
      AND   retour is     null
      ORDER BY depart_theorique DESC
      LIMIT 1
		)
ORDER BY q.last_name ASC
";
    $sql = null;
    switch ($searchType)
    {
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
    $result = $stmt->execute([]);

    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new QueteurEntity($row);
    }

    $stmt->closeCursor();
    return $results;
  }



    /**
     * Return the UL_ID of the queteur with id $queteur_id
     *
     *  
     * @return the UL_ID or -1 if not found
     *
     */
    public function getQueteurUlId($queteur_id)
    {
      $sql = "
SELECT  q.`ul_id`
FROM  `queteur` q
WHERE  q.id = :queteur_id";

      $stmt = $this->db->prepare($sql);

      $result = $stmt->execute(["queteur_id" => $queteur_id]);

      if($result)
      {
        $key="ul_id";
        $data =$stmt->fetch();

        if(array_key_exists($key, $data))
        {
          return $data[$key];
        }
      }
      return -1;
    }



    /**
     * Get one queteur by its ID
     *
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
        q.`qr_code_printed`
FROM  `queteur` q
WHERE  q.id = :queteur_id
";

        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute(["queteur_id" => $queteur_id]);

        if($result)
        {
          $queteur = new QueteurEntity($stmt->fetch());
          $stmt->closeCursor();
          return $queteur;
        }
    }


  /**
   * Update one queteur
   *
   * @param QueteurEntity $queteur The queteur to update
   * @return QueteurEntity  The queteur
   */
    public function update(QueteurEntity $queteur)
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
WHERE `id` = :id;
";

      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        "first_name" => $queteur->first_name,
        "last_name"  => $queteur->last_name,
        "email"      => $queteur->email,
        "secteur"    => $queteur->secteur,
        "nivol"      => $queteur->nivol,
        "mobile"     => $queteur->mobile,
        "notes"      => $queteur->notes,
        "ul_id"      => $queteur->ul_id,
        "minor"      => $queteur->minor,
        "id"         => $queteur->id
      ]);

      $this->logger->warning($stmt->rowCount());
      $stmt->closeCursor();

      if(!$result) {
          throw new Exception("could not save record");
      }
    }



  /**
   * Insert one queteur
   *
   * @param QueteurEntity $queteur The queteur to update
   * @return int the primary key of the new queteur
   */
  public function insert(QueteurEntity $queteur)
  {

    //TODO update query to match new columns
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
  `minor`
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
 false
);
";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $result = $stmt->execute([
      "first_name" => $queteur->first_name,
      "last_name"  => $queteur->last_name,
      "email"      => $queteur->email,
      "secteur"    => $queteur->secteur,
      "nivol"      => $queteur->nivol,
      "mobile"     => $queteur->mobile,
      "notes"      => $queteur->notes,
      "ul_id"      => $queteur->ul_id
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row  = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];
    $this->logger->info('$lastInsertId:', [$lastInsertId]) ;

    $stmt->closeCursor();
    $this->db->commit();
    return $lastInsertId;
  }
}
