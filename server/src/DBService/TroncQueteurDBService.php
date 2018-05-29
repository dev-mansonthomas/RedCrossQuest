<?php
namespace RedCrossQuest\DBService;


use Carbon\Carbon;

use RedCrossQuest\Entity\TroncInUseEntity;
use \RedCrossQuest\Entity\TroncQueteurEntity;
use PDOException;


class TroncQueteurDBService extends DBService
{

  /**
   * Get the last tronc_queteur for the tronc_id
   *
   * @param int $tronc_id The ID of the tronc
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return TroncQueteurEntity  The tronc
   * @throws \Exception if tronc not found
   * @throws PDOException if the query fails to execute on the server
   */
  public function getLastTroncQueteurByTroncId(int $tronc_id, int $ulId, $roleId)
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
 `deleted`                     ,
 `coins_money_bag_id`          ,
 `bills_money_bag_id`          ,
 `don_cb_sans_contact_amount`  ,
 `don_cb_sans_contact_number`  ,
 `don_cb_total_number`         ,       
 `don_cheque_number`         
FROM  `tronc_queteur` as t, 
      `queteur` as q
WHERE  t.tronc_id   = :tronc_id
AND    t.queteur_id = q.id
AND    q.ul_id      = :ul_id
ORDER BY id DESC
LIMIT 1
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["tronc_id" => $tronc_id, "ul_id" => $ulId]);

    if($stmt->rowCount() == 1 )
    {
      $tronc = new TroncQueteurEntity($stmt->fetch(), $this->logger);
    }
    else
    {
      $tronc = new TroncQueteurEntity(["tronc_id"=>$tronc_id, "rowCount"=>$stmt->rowCount()], $this->logger);
    }
    $stmt->closeCursor();
    return $tronc;
  }


  /**
   * Get all tronc_queteur for the tronc_id
   *
   * @param int $tronc_id The ID of the tronc
   * @param int $ulId the id of the unite locale  (join with queteur table)
   * @return TroncQueteurEntity[]  The tronc
   * @throws \Exception if tronc not found
   * @throws PDOException if the query fails to execute on the server
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
 `deleted`                     ,
 `coins_money_bag_id`          ,
 `bills_money_bag_id`          ,
 `don_cb_sans_contact_amount`  ,
 `don_cb_sans_contact_number`  ,
 `don_cb_total_number`         ,       
 `don_cheque_number`         

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
   * @throws PDOException if the query fails to execute on the server
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
 `deleted`                     ,
 `coins_money_bag_id`          ,
 `bills_money_bag_id`          ,
 `don_cb_sans_contact_amount`  ,
 `don_cb_sans_contact_number`  ,
 `don_cb_total_number`         ,       
 `don_cheque_number`         

FROM  `tronc_queteur` as t, 
      `queteur`       as q
WHERE  t.id         = :id
AND    t.queteur_id = q.id
AND    q.ul_id      = :ul_id

";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["id" => $id, "ul_id" => $ulId]);

    if($stmt->rowCount() == 1 )
    {
      $tronc = new TroncQueteurEntity($stmt->fetch(), $this->logger);
      $stmt->closeCursor();
      return $tronc;
    }
    else
    {
      $stmt->closeCursor();
      throw new \Exception("Tronc Queteur with ID:'".$id . "' not found");
    }
  }

  /**
   * Get all tronc form  queteur ID
   *
   * @param int $queteur_id The ID of the queteur
   * @param int $ulId the Id of the Unite Local
   * @return TroncQueteurEntity[] list of Tronc of the queteur
   * @throws PDOException if the query fails to execute on the server
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
 `don_creditcard`     ,
 `deleted`            ,
 `coins_money_bag_id` ,
 `bills_money_bag_id` ,
`don_cb_sans_contact_amount`   ,
 `don_cb_sans_contact_number`  ,
 `don_cb_total_number`         ,       
 `don_cheque_number`         

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
   * @throws PDOException if the query fails to execute on the server
   */
  public function setDepartToNow(int $tronc_queteur_id, int $ulId, int $userId)
  {
    $sql = "
UPDATE `tronc_queteur`           tq
      INNER JOIN  queteur         q
      ON          tq.queteur_id = q.id
SET    `depart`             = :depart,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE tq.`id`               = :id
AND    q.ul_id              = :ul_id
";
    $currentDate = new Carbon();
    $currentDate->tz='UTC';

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "depart"            => $currentDate->format('Y-m-d H:i:s'),
      "userId"            => $userId,
      "id"                => $tronc_queteur_id,
      "ul_id"             => $ulId
    ]);

    $stmt->closeCursor();

    return $currentDate->setTimezone("Europe/Paris");

  }



  /**
   * Update one tronc with Date Depart set to custom time. For the use case : préparation=>forget depart => retour
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param  int $ulId the Id of the Unite Local
   * @param  int $userId id of the user performing the operation
   * @throws PDOException if the query fails to execute on the server
   */
  public function setDepartToCustomDate(TroncQueteurEntity $tq, int $ulId, int $userId)
  {
    $sql = "
UPDATE `tronc_queteur`           tq
      INNER JOIN  queteur         q
      ON          tq.queteur_id = q.id
SET    `depart`             = :depart,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE tq.`id`               = :id
AND    q.ul_id              = :ul_id
";


    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "depart"            => $tq->depart->format('Y-m-d H:i:s'),
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId
    ]);

    $stmt->closeCursor();

  }


  /**
   * Cancel tronc departure.
   * Update one tronc and set 'depart' to null if retour is null as well
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param  int $ulId the Id of the Unite Local
   * @param  int $userId id of the user performing the operation
   * @return int the number of row updated
   * @throws PDOException if the query fails to execute on the server
   */
  public function cancelDepart(TroncQueteurEntity $tq, int $ulId, int $userId)
  {
    $sql = "
UPDATE `tronc_queteur`           tq
      INNER JOIN  queteur         q
      ON          tq.`queteur_id` = q.id
SET    `depart`             = null,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE tq.`id`               = :id
AND   tq.`retour`           is null
AND    q.`ul_id`            = :ul_id
";


    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId
    ]);

    $numberOfUpdateRows = $stmt->rowCount();

    $stmt->closeCursor();

    return $numberOfUpdateRows;
  }

  /**
   * Cancel tronc retour.
   * Update one tronc and set 'retour' to null if comptage is null as well
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param  int $ulId the Id of the Unite Local
   * @param  int $userId id of the user performing the operation
   * @return int the number of row updated
   * @throws PDOException if the query fails to execute on the server
   */
  public function cancelRetour(TroncQueteurEntity $tq, int $ulId, int $userId)
  {
    $sql = "
UPDATE `tronc_queteur`           tq
      INNER JOIN  queteur         q
      ON          tq.`queteur_id` = q.id
SET    `retour`             = null,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE tq.`id`               = :id
AND   tq.`comptage`         is null
AND    q.`ul_id`            = :ul_id
";


    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId
    ]);

    $numberOfUpdateRows = $stmt->rowCount();

    $stmt->closeCursor();

    return $numberOfUpdateRows;
  }

  /**
   * Update one tronc
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param int $ulId the Id of the unite locale
   * @param int $userId id of the user performing the operation
   * @return Carbon the date of the udpate
   * @throws \Exception if query fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function updateRetour(TroncQueteurEntity $tq, int $ulId, int $userId)
  {
    $sql = "
UPDATE `tronc_queteur`            tq
      INNER JOIN  queteur         q
      ON          tq.queteur_id = q.id
SET    `retour`               = :retour,
       `last_update`          = NOW(),
       `last_update_user_id`  = :userId,
       `notes_retour`         = :notes_retour            
WHERE tq.`id`                 = :id
AND    q.ul_id                = :ul_id
";
    $currentDate = new Carbon();
    $stmt        = $this->db->prepare($sql);
    $stmt->execute([
      "retour"            => $tq->retour->format('Y-m-d H:i:s'),
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId,
      "notes_retour"      => $tq->notes_retour
    ]);


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
   * @throws PDOException if the query fails to execute on the server
   */
    public function updateCoinsCount(TroncQueteurEntity $tq, bool $adminMode, int $ulId, int $userId)
    {

      if(!$tq->isMoneyFilled())
      {// si pas de données sur l'argent, on ne fait pas l'update
        return;
      }

      $comptage = "";
      if(!$adminMode)
      {// do not overwrite the comptage date in admin mode
        $comptage = " `comptage`                     = NOW(),";
      }
      else
      {// admin mode
        if($tq->isMoneyFilled())
        {
          //coalesce: si comptage est null, alors prends now()
          // cela permet de Si les données argent sont rempli
          //  qu'il n'y a pas de date de comptage de rempli, alors on met a jour la date de comptage.
          //  S'il y avait déjà une date, on ne la met pas à jour (il y a last_update et la table d'historisation)
          //
          $comptage = " `comptage`                     = COALESCE(comptage, NOW()),";
        }
      }

      $this->logger->warning($tq->notes_retour_comptage_pieces);


      $sql = "
UPDATE `tronc_queteur`            tq
      INNER JOIN  queteur         q
      ON          tq.queteur_id = q.id
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
 `don_cheque`                   = :don_cheque,
$comptage
 `last_update`                  = NOW(),
 `last_update_user_id`          = :userId,
 `notes_retour_comptage_pieces` = :notes_retour_comptage_pieces, 
 `coins_money_bag_id`           = :coins_money_bag_id,
 `bills_money_bag_id`           = :bills_money_bag_id,       
 `don_cheque_number`            = :don_cheque_number        

WHERE tq.`id`                   = :id
AND    q.ul_id                  = :ul_id
";

      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        "euro500"                       => $tq->euro500,
        "euro200"                       => $tq->euro200,
        "euro100"                       => $tq->euro100,
        "euro50"                        => $tq->euro50 ,
        "euro20"                        => $tq->euro20 ,
        "euro10"                        => $tq->euro10 ,
        "euro5"                         => $tq->euro5  ,
        "euro2"                         => $tq->euro2  ,
        "euro1"                         => $tq->euro1  ,
        "cents50"                       => $tq->cents50,
        "cents20"                       => $tq->cents20,
        "cents10"                       => $tq->cents10,
        "cents5"                        => $tq->cents5 ,
        "cents2"                        => $tq->cents2 ,
        "cent1"                         => $tq->cent1  ,
        "foreign_coins"                 => $tq->foreign_coins,
        "foreign_banknote"              => $tq->foreign_banknote,
        "don_cheque"                    => $tq->don_cheque,
        "userId"                        => $userId,
        "id"                            => $tq->id,
        "ul_id"                         => $ulId,
        "notes_retour_comptage_pieces"  => $tq->notes_retour_comptage_pieces,
        "coins_money_bag_id"            => $tq->coins_money_bag_id,
        "bills_money_bag_id"            => $tq->bills_money_bag_id,
        "don_cheque_number"             => $tq->don_cheque_number
      ]);

      $stmt->closeCursor();
    }

  /**
   * Update one tronc for credit card data
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param boolean             $adminMode  If the user performing the action is administrator
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws \Exception if the query fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function updateCreditCardCount(TroncQueteurEntity $tq, bool $adminMode , int $ulId, int $userId)
  {
    $comptage = "";
    if(!$adminMode)
    {
      $comptage = " `comptage`                     = NOW(),";
    }

    $sql = "
UPDATE `tronc_queteur`            tq
      INNER JOIN  queteur         q
      ON          tq.queteur_id = q.id
SET
 `don_creditcard`               = :don_creditcard,
 `don_cb_sans_contact_amount`   = :don_cb_sans_contact_amount  ,
 `don_cb_sans_contact_number`   = :don_cb_sans_contact_number  ,
 `don_cb_total_number`          = :don_cb_total_number         ,  
 `notes_retour_comptage_pieces` = :notes_retour_comptage_pieces,
$comptage
 `last_update`         = NOW(),
 `last_update_user_id` = :userId,
 `notes_retour_comptage_pieces` = :notes_retour_comptage_pieces
WHERE tq.`id`          = :id
AND    q.ul_id         = :ul_id
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "don_creditcard"              => $tq->don_creditcard,
      "don_cb_sans_contact_amount"  => $tq->don_cb_sans_contact_amount,
      "don_cb_sans_contact_number"  => $tq->don_cb_sans_contact_number,
      "don_cb_total_number"         => $tq->don_cb_total_number       ,
      "userId"                      => $userId,
      "id"                          => $tq->id,
      "ul_id"                       => $ulId,
      "notes_retour_comptage_pieces"=> $tq->notes_retour_comptage_pieces
    ]);

    $stmt->closeCursor();

  }


  /**
   * Update one tronc as Admin for data like dates, and point_quete
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws \Exception if the query fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function updateTroncQueteurAsAdmin(TroncQueteurEntity $tq, int $ulId, int $userId)
  {

    //$this->logger->debug("Admin Updates dates, point_quete_id or deleted ", [$tq]);
    $sql = "
UPDATE `tronc_queteur`             tq
      INNER JOIN  `queteur`         q
      ON          tq.`queteur_id` = q.`id`
SET
  `depart_theorique`   = :depart_theorique,
  `depart`             = :depart,
  `retour`             = :retour,
  `point_quete_id`     = :point_quete_id,
  `deleted`            = :deleted,
  `last_update`        = NOW(),
  `last_update_user_id`= :userId,
  `notes_update`       = :notes_update
WHERE tq.`id`          = :id
AND    q.ul_id         = :ul_id
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "depart_theorique"  => $tq->depart_theorique,
      "depart"            => $tq->depart,
      "retour"            => $tq->retour,
      "point_quete_id"    => $tq->point_quete_id,
      "deleted"           => $tq->deleted?1:0,
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId,
      "notes_update"      => $tq->notes_update
    ]);

    //$this->logger->warning($stmt->rowCount());
    $stmt->closeCursor();

  }



  /**
   * Insert one TroncQueteur
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws \Exception if the query fails
   * @return object check the code ;)
   * @throws PDOException if the query fails to execute on the server
   * @throws \Exception if the tronc is already in use
   */
  public function insert(TroncQueteurEntity $tq, int $ulId, int $userId)
  {
    $checkTroncResult = $this->checkTroncNotAlreadyInUse($tq->tronc_id, $ulId);
    $returnInfo = (object) [
      'troncInUse'     => $checkTroncResult!= null,
      'troncInUseInfo' => $checkTroncResult
      ];

    if($checkTroncResult == null)
    {
      $queryData =
      [
        "queteur_id"            => $tq->queteur_id,
        "point_quete_id"        => $tq->point_quete_id,
        "tronc_id"              => $tq->tronc_id,
        "userId"                => $userId,
        "notes_depart_theorique"=> $tq->notes_depart_theorique
      ];

      $departTheoriqueQuery = "";

      if($tq->preparationAndDepart == true)
      {//if the user click on "Prepart And Depart", then depart_theorique is override by current date
        $departTheoriqueQuery ="  NOW(),";
      }
      else
      {
        $departTheoriqueQuery ="  :depart_theorique,";
        $queryData["depart_theorique"] = $tq->depart_theorique->format('Y-m-d H:i:s');
      }


      $sql = "
INSERT INTO `tronc_queteur`
( 
   `queteur_id`, 
   `point_quete_id`, 
   `tronc_id`, 
   `depart_theorique`,
   `last_update`,
   `last_update_user_id`,
   `notes_depart_theorique`
)
VALUES
(
  :queteur_id,
  :point_quete_id, 
  :tronc_id, 
$departTheoriqueQuery
  NOW(),
  :userId,
  :notes_depart_theorique
)
";

      $stmt = $this->db->prepare($sql);

      $this->db->beginTransaction();
      $stmt->execute($queryData);

      $stmt->closeCursor();

      $stmt = $this->db->query("select last_insert_id()");
      $row  = $stmt->fetch();

      $lastInsertId = $row['last_insert_id()'];

      $stmt->closeCursor();
      $this->db->commit();
      $returnInfo->lastInsertId=$lastInsertId;
    }


    return $returnInfo;
  }


  /****
   * when preparing the tronc, upon saving, this function is called to check if this tronc is not already associated
   * with a tronc_queteur row for which the dateDepart or dateRetour is null (which means it should be in use)
   * @param int $troncId the Id of the tronc that we want to check
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return array Array of TroncInUseEntity that contains informations of the existing use of the tronc
   * @throws PDOException if the query fails to execute on the server
   */
  public function checkTroncNotAlreadyInUse(int $troncId, int $ulId)
  {
    $sql = "
SELECT 	
tq.id, 
tq.queteur_id,
tq.tronc_id,  
tq.depart_theorique, 
tq.depart, 
q.first_name, 
q.last_name, 
q.email, 
q.mobile, 
q.nivol
FROM 	tronc_queteur tq,
      queteur        q
WHERE 	 q.ul_id      = :ul_id
AND     tq.deleted    = 0
AND     tq.tronc_id   = :tronc_id
AND     tq.queteur_id = q.id
AND    (tq.depart     is null OR 
        tq.retour     is null)
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      "tronc_id"        => $troncId,
      "ul_id"           => $ulId
    ]);

    $rowCount = $stmt->rowCount();
    $results  = null;

    if( $rowCount > 0)
    {
      $existingUseOfTronc = [];
      $i=0;
      while($row = $stmt->fetch())
      {
        $existingUseOfTronc[$i++]=new TroncInUseEntity($row);
      }
      $stmt->closeCursor();
      return $existingUseOfTronc;
    }

    $stmt->closeCursor();
    return null;// it's ok, no use of tronc
  }



   /***
    *  work with  checkTroncNotAlreadyInUse($troncId, $ulId).
    * If the use decide to delete the tronc_queteur row because the existing rows
    * represents a session of quete that has not been performed
    * @param int $troncId the Id of the tronc to be deleted
    * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
    * @param int $userId the ID of the user performing the action
    * @throws PDOException if the query fails to execute on the server
    *
    */
  public function deleteNonReturnedTroncQueteur(int $troncId, int $ulId, int $userId)
  {

    //update the tronc to deleted=1, limiting the update to the rows of the UL and set who is deleting the row
    $sql ="
UPDATE            tronc_queteur tq
      INNER JOIN  queteur       q
      ON          tq.queteur_id = q.id
SET   deleted             = 1,
      last_update_user_id = :user_id
WHERE tq.tronc_id = :tronc_id
AND    q.ul_id    = :ul_id
AND  (tq.depart IS NULL OR 
      tq.retour IS NULL)
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
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
   * @throws PDOException if the query fails to execute on the server
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
`deleted`                     ,
`coins_money_bag_id`          ,
`bills_money_bag_id`          ,
`don_cb_sans_contact_amount`  ,
`don_cb_sans_contact_number`  ,
`don_cb_total_number`         ,       
`don_cheque_number`         
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




  /**
   * Search existing money_bag id from the current year and the current UniteLocale
   *
   * @param string $query the searched string
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param string $type "bill" or "coin" depending the kind of bag the user search
   * @return string[]  list of money_bag_id that match the search
   * @throws PDOException if the query fails to execute on the server
   */
  public function searchMoneyBagId(string $query, string $type, int $ulId)
  {
    $column = "coins_money_bag_id";
    if($type == "bill")
    {
      $column = "bills_money_bag_id";
    }

    $sql = "
SELECT DISTINCT t.money_bag_id FROM
(
SELECT DISTINCT(tq.$column) as money_bag_id
FROM   tronc_queteur tq, 
              queteur        q
WHERE  tq.$column  like :query
AND YEAR(tq.depart) =  YEAR(NOW())
AND tq.queteur_id   = q.id
AND q.ul_id         = :ul_id
UNION
SELECT DISTINCT(nd.$column) as money_bag_id
FROM   named_donation nd
WHERE  nd.$column  like :query
AND YEAR(nd.donation_date) =  YEAR(NOW())
AND nd.ul_id = :ul_id
) as t
ORDER BY t.money_bag_id ASC
";

    $stmt = $this->db->prepare($sql);

    $stmt->execute(["query" => "%".$query."%", "ul_id" => $ulId]);

    $results = [];
    $i=0;
    while($row = $stmt->fetch())
    {
      $results[$i++] =  $row['money_bag_id'];
    }
    return $results;
  }



}
