<?php
namespace RedCrossQuest\DBService;


use Carbon\Carbon;

use \RedCrossQuest\Entity\TroncQueteurEntity;



class TroncQueteurDBService extends DBService
{

  /**
   * Get the last tronc_queteur for the tronc_id
   *
   * @param int $tronc_id The ID of the tronc
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return TroncQueteurEntity  The tronc
   * @throws \Exception if tronc not found
   */
  public function getLastTroncQueteurByTroncId(int $tronc_id, int $ulId)
  {
    $sql = "
SELECT 
 t.`id`               ,
 `queteur_id`         ,
 `point_quete_id`     ,
 `tronc_id`           ,
 `depart_theorique`   ,
 `depart`             ,
 `retour`             ,
 `comptage`           ,
 `last_update`        ,
 `last_update_user_id`,
 `euro500`            ,
 `euro200`            ,
 `euro100`            ,
 `euro50`             ,
 `euro20`             ,
 `euro10`             ,
 `euro5`              ,
 `euro2`              ,
 `euro1`              ,
 `cents50`            ,
 `cents20`            ,
 `cents10`            ,
 `cents5`             ,
 `cents2`             ,
 `cent1`              ,
 `foreign_coins`      ,
 `foreign_banknote`   ,
 `notes_depart_theorique`      ,
 `notes_retour`                ,
 `notes_retour_comptage_pieces`,
 `notes_update`                ,
 `don_cheque`                  ,
 `don_creditcard`              ,
 `deleted`
FROM  `tronc_queteur` as t, 
      `queteur` as q
WHERE  t.tronc_id   = :tronc_id
AND    t.queteur_id = q.id
AND    q.ul_id      = :ul_id
ORDER BY id DESC
LIMIT 1
";

    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute(["tronc_id" => $tronc_id, "ul_id" => $ulId]);

    if($result && $stmt->rowCount() == 1 )
    {
      $tronc = new TroncQueteurEntity($stmt->fetch(), $this->logger);
      $stmt->closeCursor();
      return $tronc;
    }
    else
    {
      throw new \Exception("Tronc Queteur with ID:'".$tronc_id . "' not found");
    }
  }


  /**
   * Get all tronc_queteur for the tronc_id
   *
   * @param int $tronc_id The ID of the tronc
   * @param int $ulId the id of the unite locale  (join with queteur table)
   * @return TroncQueteurEntity[]  The tronc
   * @throws \Exception if tronc not found
   */
  public function getTroncsQueteurByTroncId(int $tronc_id, int $ulId)
  {
    $sql = "
SELECT 
 t.`id`              ,
 `queteur_id`        ,
 `point_quete_id`    ,
 `tronc_id`          ,
 `depart_theorique`  ,
 `depart`            ,
 `retour`            ,
 `comptage`           ,
 `last_update`        ,
 `last_update_user_id`,
 `euro500`           ,
 `euro200`           ,
 `euro100`           ,
 `euro50`            ,
 `euro20`            ,
 `euro10`            ,
 `euro5`             ,
 `euro2`             ,
 `euro1`             ,
 `cents50`           ,
 `cents20`           ,
 `cents10`           ,
 `cents5`            ,
 `cents2`            ,
 `cent1`             ,
 `foreign_coins`     ,
 `foreign_banknote`  ,
 `notes_depart_theorique`      ,
 `notes_retour`                ,
 `notes_retour_comptage_pieces`,
 `notes_update`                ,
 q.`last_name`                 ,
 q.`first_name`                ,
 `don_cheque`                  ,
 `don_creditcard`              ,
 `deleted`
FROM  `tronc_queteur` as t, 
      `queteur` as q
WHERE  t.tronc_id   = :tronc_id
AND    t.queteur_id = q.id
AND    q.ul_id      = :ul_id
ORDER BY t.id DESC

";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["tronc_id" => $tronc_id, "ul_id" => $ulId]);

    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new TroncQueteurEntity($row, $this->logger);
    }
    return $results;
  }



  /**
   * Get one tronc_queteur by its ID
   *
   * @param int $id The ID of the tronc_quete row
   * @param int $ulId the Id of the Unite Local
   * @return TroncQueteurEntity  The tronc
   * @throws \Exception if tronc_queteur not found
   */
  public function getTroncQueteurById(int $id, int $ulId)
  {
    $sql = "
SELECT 
t.`id`                ,
 `queteur_id`         ,
 `point_quete_id`     ,
 `tronc_id`           ,
 `depart_theorique`   ,
 `depart`             ,
 `retour`             ,
 `comptage`           ,
 `last_update`        ,
 `last_update_user_id`,
 `euro500`            ,
 `euro200`            ,
 `euro100`            ,
 `euro50`             ,
 `euro20`             ,
 `euro10`             ,
 `euro5`              ,
 `euro2`              ,
 `euro1`              ,
 `cents50`            ,
 `cents20`            ,
 `cents10`            ,
 `cents5`             ,
 `cents2`             ,
 `cent1`              ,
 `foreign_coins`      ,
 `foreign_banknote`   ,
 `notes_depart_theorique`      ,
 `notes_retour`                ,
 `notes_retour_comptage_pieces`,
 `notes_update`                ,
 `don_cheque`                  ,
 `don_creditcard`              ,
 `deleted`
FROM  `tronc_queteur` as t, 
      `queteur`       as q
WHERE  t.id         = :id
AND    t.queteur_id = q.id
AND    q.ul_id      = :ul_id

";

    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute(["id" => $id, "ul_id" => $ulId]);

    if($result && $stmt->rowCount() == 1 )
    {
      $tronc = new TroncQueteurEntity($stmt->fetch(), $this->logger);
      $stmt->closeCursor();
      return $tronc;
    }
    else
    {
      throw new \Exception("Tronc Queteur with ID:'".$id . "' not found");
    }
  }

  /**
   * Get all tronc form  queteur ID
   *
   * @param int $queteur_id The ID of the queteur
   * @param int $ulId the Id of the Unite Local
   * @return TroncQueteurEntity[] list of Tronc of the queteur
   */
  public function getTroncsQueteur(int $queteur_id, int $ulId)
  {
    $sql = "
SELECT 
t.`id`                ,
 `queteur_id`         ,
 `point_quete_id`     ,
 `tronc_id`           ,
 `depart_theorique`   ,
 `depart`             ,
 `retour`             ,
 `comptage`           ,
 `last_update`        ,
 `last_update_user_id`,
 `euro500`            ,
 `euro200`            ,
 `euro100`            ,
 `euro50`             ,
 `euro20`             ,
 `euro10`             ,
 `euro5`              ,
 `euro2`              ,
 `euro1`              ,
 `cents50`            ,
 `cents20`            ,
 `cents10`            ,
 `cents5`             ,
 `cents2`             ,
 `cent1`              ,
 `foreign_coins`      ,
 `foreign_banknote`   ,
 `don_cheque`         ,
 `don_creditcard`  
FROM  `tronc_queteur` as t, 
      `queteur`       as q
WHERE  t.queteur_id = :queteur_id
AND    t.queteur_id = q.id
AND    q.ul_id      = :ul_id
ORDER BY t.id desc 
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(["queteur_id" => $queteur_id, "ul_id" => $ulId]);


    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new TroncQueteurEntity($row, $this->logger);
    }
    return $results;
  }

  /**
   * Update one tronc with Date Depart set to current time
   *
   * @param  int $tronc_queteur_id : the id of tronc_queteur table to update
   * @param  int $ulId the Id of the Unite Local
   * @param  int $userId id of the user performing the operation
   * @return Carbon the date that has been set to tronc_queteur.dateDepart
   * @throws \Exception if update fails
   */
  public function setDepartToNow(int $tronc_queteur_id, int $ulId, int $userId)
  {

//TODO limit the query by UL_ID
    $sql = "
UPDATE `tronc_queteur`
SET    `depart`             = :depart,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE  `id`                 = :id
";
    $currentDate = new Carbon();
    $currentDate->tz='UTC';

    $stmt        = $this->db->prepare($sql);
    $result      = $stmt->execute([
      "depart"            => $currentDate->format('Y-m-d H:i:s'),
      "userId"            => $userId,
      "id"                => $tronc_queteur_id
    ]);

    $stmt->closeCursor();

    if(!$result) {
      throw new \Exception("could not save record");
    }
    //$this->logger->info("Depart Tronc $tronc_queteur_id='".$tronc_queteur_id."' with date : '".$currentDate->format('Y-m-d H:i:s')."'', numRows updated : ".$stmt->rowCount());

    return $currentDate->setTimezone("Europe/Paris");

  }


  /**
   * Update one tronc
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param int $ulId the Id of the unite locale
   * @param int $userId id of the user performing the operation
   * @return Carbon the date of the udpate
   * @throws \Exception if query fails
   */
  public function updateRetour(TroncQueteurEntity $tq, int $ulId, int $userId)
  {
    $sql = "
UPDATE `tronc_queteur`
SET    `retour`               = :retour,
       `last_update`          = NOW(),
       `last_update_user_id`  = :userId            
WHERE  `id`                   = :id
";
    $currentDate = new Carbon();
    $stmt        = $this->db->prepare($sql);
    $result      = $stmt->execute([
      "retour"            => $tq->retour->format('Y-m-d H:i:s'),
      "userId"            => $userId,
      "id"                => $tq->id
    ]);

    if(!$result)
    {
      throw new \Exception("could not save record");
    }

    //$this->logger->warning($stmt->rowCount());
    $stmt->closeCursor();

    return $currentDate;

  }

  /**
   * Update one tronc
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param boolean             $adminMode  If the user performing the action is administrator
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws \Exception if the query fails
   */
    public function updateCoinsCount(TroncQueteurEntity $tq, boolean $adminMode, int $ulId, int $userId)
    {
      $comptage = "";
      if($adminMode != true)
      {// do not overwrite the comptage date in admin mode
        $comptage = " `comptage`                     = NOW(),";
      }

      //$this->logger->debug("Saving coins adminMode='$adminMode' $comptage", [$tq]);
      $sql = "
UPDATE `tronc_queteur`
SET
 `euro500`                      = :euro500,           
 `euro200`                      = :euro200,           
 `euro100`                      = :euro100,           
 `euro50`                       = :euro50 ,           
 `euro20`                       = :euro20 ,           
 `euro10`                       = :euro10 ,           
 `euro5`                        = :euro5  ,           
 `euro2`                        = :euro2  ,           
 `euro1`                        = :euro1  ,           
 `cents50`                      = :cents50,           
 `cents20`                      = :cents20,           
 `cents10`                      = :cents10,           
 `cents5`                       = :cents5 ,           
 `cents2`                       = :cents2 ,           
 `cent1`                        = :cent1  ,           
 `foreign_coins`                = :foreign_coins,     
 `foreign_banknote`             = :foreign_banknote,  
 `notes_retour_comptage_pieces` = :notes,
 `don_cheque`                   = :don_cheque,
$comptage
 `last_update`                  = NOW(),
 `last_update_user_id`          = :userId            
 WHERE `id` = :id;
";

      $stmt = $this->db->prepare($sql);
      $result = $stmt->execute([
        "euro500"           => $tq->euro500,
        "euro200"           => $tq->euro200,
        "euro100"           => $tq->euro100,
        "euro50"            => $tq->euro50 ,
        "euro20"            => $tq->euro20 ,
        "euro10"            => $tq->euro10 ,
        "euro5"             => $tq->euro5  ,
        "euro2"             => $tq->euro2  ,
        "euro1"             => $tq->euro1  ,
        "cents50"           => $tq->cents50,
        "cents20"           => $tq->cents20,
        "cents10"           => $tq->cents10,
        "cents5"            => $tq->cents5 ,
        "cents2"            => $tq->cents2 ,
        "cent1"             => $tq->cent1  ,
        "foreign_coins"     => $tq->foreign_coins,
        "foreign_banknote"  => $tq->foreign_banknote,
        "notes"             => $tq->notes,
        "don_cheque"        => $tq->don_cheque,
        "userId"            => $userId,
        "id"                => $tq->id
      ]);

      //$this->logger->warning($stmt->rowCount());
      $stmt->closeCursor();

      if(!$result)
      {
        throw new \Exception("could not save record");
      }
    }

  /**
   * Update one tronc for credit card data
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param boolean             $adminMode  If the user performing the action is administrator
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws \Exception if the query fails
   */
  public function updateCreditCardCount(TroncQueteurEntity $tq, boolean $adminMode , int $ulId, int $userId)
  {
    $comptage = "";
    if($adminMode != true)
    {
      $comptage = " `comptage`                     = NOW(),";
    }

    //$this->logger->debug("Saving Credit Card adminMode='$adminMode'", [$tq]);
    $sql = "
UPDATE `tronc_queteur`
SET
 `don_creditcard`               = :don_creditcard,  
 `notes_retour_comptage_pieces` = :notes,
$comptage
 `last_update`         = NOW(),
 `last_update_user_id` = :userId 
 WHERE `id` = :id;
";

    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      "don_creditcard"    => $tq->don_creditcard,
      "notes"             => $tq->notes,
      "userId"            => $userId,
      "id"                => $tq->id
    ]);

    //$this->logger->warning($stmt->rowCount());
    $stmt->closeCursor();

    if(!$result) {
      throw new \Exception("could not save record");
    }
  }


  /**
   * Update one tronc as Admin for data like dates, and point_quete
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws \Exception if the query fails
   */
  public function updateTroncQueteurAsAdmin(TroncQueteurEntity $tq, int $ulId, int $userId)
  {

    //$this->logger->debug("Admin Updates dates, point_quete_id or deleted ", [$tq]);
    $sql = "
UPDATE `tronc_queteur`
SET
  `depart_theorique`   = :depart_theorique,
  `depart`             = :depart,
  `retour`             = :retour,
  `point_quete_id`     = :point_quete_id,
  `deleted`            = :deleted,
  `last_update`        = NOW(),
  `last_update_user_id`= :userId 
 WHERE `id` = :id;
";

    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      "depart_theorique"  => $tq->depart_theorique,
      "depart"            => $tq->depart,
      "retour"            => $tq->retour,
      "point_quete_id"    => $tq->point_quete_id,
      "deleted"           => $tq->deleted?1:0,
      "userId"            => $userId,
      "id"                => $tq->id
    ]);

    //$this->logger->warning($stmt->rowCount());
    $stmt->closeCursor();

    if(!$result) {
      throw new \Exception("could not save record");
    }
  }



  /**
   * Insert one TroncQueteur
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws \Exception if the query fails
   * @return int the primary key of the new tronc
   */
  public function insert(TroncQueteurEntity $tq, int $ulId, int $userId)
  {


    $checkTroncResult = $this->checkTroncNotAlreadyInUse($tq->tronc_id, $ulId);

    if($checkTroncResult != null)
    {
      throw new \Exception($checkTroncResult);
    }

    $sql = "
INSERT INTO `tronc_queteur`
( 
   `queteur_id`, 
   `point_quete_id`, 
   `tronc_id`, 
   `depart_theorique`,
   `last_update`,
   `last_update_user_id`       
)
VALUES
(
  :queteur_id,
  :point_quete_id, 
  :tronc_id, 
  :depart_theorique,
  NOW(),
  :userId
)
";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $result = $stmt->execute([
      "queteur_id"        => $tq->queteur_id,
      "point_quete_id"    => $tq->point_quete_id,
      "tronc_id"          => $tq->tronc_id,
      "depart_theorique"  => $tq->depart_theorique->format('Y-m-d H:i:s'),
      "userId"            => $userId
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row  = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];
    //$this->logger->info('$lastInsertId:', [$lastInsertId]) ;

    $stmt->closeCursor();
    $this->db->commit();
    return $lastInsertId;
  }


  /****
   * when preparing the tronc, upon saving, this function is called to check if this tronc is not already associated
   * with a tronc_queteur row for which the dateDepart or dateRetour is null (which means it should be in use)
   * @param int $troncId the Id of the tronc that we want to check
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return string an html table to be displayed in case the tronc is already in use
   */
  public function checkTroncNotAlreadyInUse(int $troncId, int $ulId)
  {
    $sql = "
SELECT 	
tq.id, 
tq.queteur_id, 
tq.depart_theorique, 
tq.depart, 
q.first_name, 
q.last_name, 
q.email, 
q.mobile, 
q.nivol
FROM 	tronc_queteur tq,
      queteur q
WHERE 	q.ul_id       = :ul_id
AND     tq.deleted    = 0
AND     tq.tronc_id   = :tronc_id
AND     tq.queteur_id = q.id
AND    (tq.depart     is null OR 
        tq.retour     is null)
";

    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      "tronc_id"        => $troncId,
      "ul_id"           => $ulId
    ]);

    $rowCount = $stmt->rowCount();
    $results  = null;

    if( $rowCount > 0)
    {
      $results = "
<div class=\"panel panel-default\">
<!-- Default panel contents -->
  <div class=\"panel-heading\">Ce tronc est comptabilisé comme étant en cours d'utilisation <b>".$rowCount."</b> fois</div>

  <table class='table'>
  <thead>
    <tr>
      <th>tronc_queteur ID</th>
      <th>départ Théorique</th>
      <th>départ</th>
      <th>queteur id</th>
      <th>Prénom</th>
      <th>Nom</th>
      <th>email</th>
      <th>mobile</th>
      <th>nivol</th>
    </tr>
   </thead>
   <tbody>
    ";

      while($row = $stmt->fetch())
      {

        $departTheorique = new Carbon($row['depart_theorique'], 'UTC');
        $departTheorique->setTimezone('Europe/Paris');

        $results .=  "
      <tr>
        <th>".$row['id']."</th>
        <td>".$departTheorique."</td>
        <td>".($row['depart']!=null ? (new Carbon($row['depart'], 'UTC'))->setTimezone('Europe/Paris'):null)."</td>
        <td>".$row['queteur_id']."</td>
        <td>".$row['first_name']."</td>
        <td>".$row['last_name']."</td>
        <td>".$row['email']."</td>
        <td>".$row['mobile']."</td>
        <td>".$row['nivol']."</td>
      </tr>";
    }
      $results .= "
    </tbody>
  </table>
</div>
";

    }
    $stmt->closeCursor();
    return $results;
  }



   /***
    *  work with  checkTroncNotAlreadyInUse($troncId, $ulId).
    * If the use decide to delete the tronc_queteur row because the existing rows
    * represents a session of quete that has not been performed
    * @param int $troncId the Id of the tronc to be deleted
    * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
    * @param int $userId the ID of the user performing the action
    *
    */
  public function deleteNonReturnedTroncQueteur(int $troncId, int $ulId, int $userId)
  {

    //update the tronc to deleted=1, limiting the update to the rows of the UL and set who is deleting the row
    $sql ="
update            tronc_queteur tq
      inner join  queteur       q
      on          tq.queteur_id = q.id
set   deleted             = 0,
      last_update_user_id = :user_id
where tq.tronc_id = :tronc_id
and    q.ul_id    = :ul_id
AND  (tq.depart IS NULL OR 
      tq.retour IS NULL)
";

    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      "tronc_id"        => $troncId,
      "ul_id"           => $ulId   ,
      "user_id"         => $userId
    ]);


    $stmt->closeCursor();
    //$this->logger->info("deleted ".$rowCount." non returned tronc_queteur with tronc_id='".$troncId."'");
  }



  /**
   * Get all previous version of a tronc_queteur by its id
   *
   * @param int $id The ID of the tronc_quete for which we want the history
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return TroncQueteurEntity[]  list of tronc_queteur entity (history mode)
   * @throws \Exception if tronc_queteur not found
   */
  public function getTroncQueteurHistoryById(int $id, int $ulId)
  {
    $sql = "
SELECT 
t.`id`                        ,
`insert_date`                 ,
`tronc_queteur_id`            ,
`queteur_id`                  ,
`point_quete_id`              ,
`tronc_id`                    ,
`depart_theorique`            ,
`depart`                      ,
`retour`                      ,
`comptage`                    ,
`last_update`                 ,
`last_update_user_id`         ,
`euro500`                     ,
`euro200`                     ,
`euro100`                     ,
`euro50`                      ,
`euro20`                      ,
`euro10`                      ,
`euro5`                       ,
`euro2`                       ,
`euro1`                       ,
`cents50`                     ,
`cents20`                     ,
`cents10`                     ,
`cents5`                      ,
`cents2`                      ,
`cent1`                       ,
`foreign_coins`               ,
`foreign_banknote`            ,
`notes_depart_theorique`      ,
`notes_retour`                ,
`notes_retour_comptage_pieces`,
`notes_update`                ,
`don_cheque`                  ,
`don_creditcard`              ,
`deleted`
FROM  `tronc_queteur_historique`  as t, 
      `queteur`                   as q
WHERE  t.tronc_queteur_id = :tronc_queteur_id
AND    t.queteur_id       = q.id
AND    q.ul_id            = :ul_id
ORDER BY t.id DESC
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["tronc_queteur_id" => $id, "ul_id" => $ulId]);

    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  new TroncQueteurEntity($row, $this->logger);
    }
    return $results;
  }


}
