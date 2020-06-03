<?php
namespace RedCrossQuest\DBService;


use Carbon\Carbon;
use Exception;
use PDOException;
use RedCrossQuest\Entity\TroncInUseEntity;
use RedCrossQuest\Entity\TroncQueteurEntity;
use RedCrossQuest\routes\routesActions\troncsQueteurs\PrepareTroncQueteurResponse;


class TroncQueteurDBService extends DBService
{

  /**
   * Get the last tronc_queteur for the tronc_id
   *
   * @param int $tronc_id The ID of the tronc
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return TroncQueteurEntity  The tronc
   * @throws Exception if tronc not found
   * @throws PDOException if the query fails to execute on the server
   */
  public function getLastTroncQueteurByTroncId(int $tronc_id, int $ulId):TroncQueteurEntity
  {
    $sql = "
SELECT 
 t.`id`               ,
 t.`ul_id`            ,
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
 `don_cb_total_number`         ,       
 `don_cheque_number`         
FROM  `tronc_queteur` as t
WHERE  t.tronc_id   = :tronc_id
AND    t.ul_id      = :ul_id
AND    t.deleted    = 0
ORDER BY id DESC
LIMIT 1
";
    $parameters = ["tronc_id" => $tronc_id, "ul_id" => $ulId];

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new TroncQueteurEntity($row, $this->logger);
    }, true);
  }


  /**
   * Get all tronc_queteur for the tronc_id
   *
   * @param int $tronc_id The ID of the tronc
   * @param int $ulId the id of the unite locale  (join with queteur table)
   * @return TroncQueteurEntity[]  The tronc
   * @throws Exception if tronc not found
   * @throws PDOException if the query fails to execute on the server
   */
  public function getTroncsQueteurByTroncId(int $tronc_id, int $ulId):array
  {
    $sql = "
SELECT 
 t.`id`              ,
 t.`ul_id`           ,
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
 `don_cb_total_number`         ,       
 `don_cheque_number`         

FROM  `tronc_queteur` as t, 
      `queteur` as q
WHERE  t.tronc_id   = :tronc_id
AND    t.queteur_id = q.id
AND    t.ul_id      = :ul_id
ORDER BY t.id DESC

";
    $parameters = ["tronc_id" => $tronc_id, "ul_id" => $ulId];
    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new TroncQueteurEntity($row, $this->logger);
    });
  }



  /**
   * Get one tronc_queteur by its ID
   *
   * @param int $id The ID of the tronc_quete row
   * @param int $ulId the Id of the Unite Local
   * @return TroncQueteurEntity  The tronc
   * @throws Exception if tronc_queteur not found
   * @throws PDOException if the query fails to execute on the server
   */
  public function getTroncQueteurById(int $id, int $ulId):TroncQueteurEntity
  {
    $sql = "
SELECT 
t.`id`                ,
t.`ul_id`             ,
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
 `don_cb_total_number`         ,       
 `don_cheque_number`         
FROM  `tronc_queteur` as t
WHERE  t.id         = :id
AND    t.ul_id      = :ul_id

";
    $parameters = ["id" => $id, "ul_id" => $ulId];
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new TroncQueteurEntity($row, $this->logger);
    }, true);
  }

  /**
   * Get all tronc form queteur ID
   *
   * @param int $queteur_id The ID of the queteur
   * @param int $ulId the Id of the Unite Local
   * @return TroncQueteurEntity[] list of Tronc of the queteur
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getTroncsQueteur(int $queteur_id, int $ulId):array
  {
    $sql = "
SELECT 
t.`id`                ,
t.`ul_id`             ,
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
 `don_cb_total_number`         ,       
 `don_cheque_number`         

FROM  `tronc_queteur` as t
WHERE  t.queteur_id = :queteur_id
AND    t.ul_id      = :ul_id
ORDER BY t.id desc 
";

    $parameters = ["queteur_id" => $queteur_id, "ul_id" => $ulId];
    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new TroncQueteurEntity($row, $this->logger);
    });
  }

  /**
   * Update one tronc with Date Depart set to current time
   *
   * @param int $tronc_queteur_id : the id of tronc_queteur table to update
   * @param int $ulId the Id of the Unite Local
   * @param int $userId id of the user performing the operation
   * @return Carbon the date that has been set to tronc_queteur.dateDepart
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function setDepartToNow(int $tronc_queteur_id, int $ulId, int $userId):Carbon
  {
    $sql = "
UPDATE `tronc_queteur`           tq
SET    `depart`             = :depart,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE tq.`id`               = :id
AND   tq.ul_id              = :ul_id
";
    $currentDate = Carbon::now();
    $currentDate->tz='UTC';

    $parameters = [
      "depart"            => $currentDate->format('Y-m-d H:i:s'),
      "userId"            => $userId,
      "id"                => $tronc_queteur_id,
      "ul_id"             => $ulId
    ];

    $this->executeQueryForUpdate($sql, $parameters);

    return $currentDate->setTimezone("Europe/Paris");
  }


  /**
   * Update one tronc with Date Depart set to custom time. For the use case : préparation=>forget depart => retour
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param int $ulId the Id of the Unite Local
   * @param int $userId id of the user performing the operation
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function setDepartToCustomDate(TroncQueteurEntity $tq, int $ulId, int $userId):void
  {
    $sql = "
UPDATE `tronc_queteur`        tq
SET    `depart`             = :depart,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE tq.`id`               = :id
AND   tq.ul_id              = :ul_id
";

    $parameters = [
     "depart"            => $tq->depart->format('Y-m-d H:i:s'),
     "userId"            => $userId,
     "id"                => $tq->id,
     "ul_id"             => $ulId
   ];

    $this->executeQueryForUpdate($sql, $parameters);
  }


  /**
   * Cancel tronc departure.
   * Update one tronc and set 'depart' to null if retour is null as well
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param int $ulId the Id of the Unite Local
   * @param int $userId id of the user performing the operation
   * @return int the number of row updated
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function cancelDepart(TroncQueteurEntity $tq, int $ulId, int $userId):int
  {
    $sql = "
UPDATE `tronc_queteur`           tq
SET    `depart`             = null,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE tq.`id`               = :id
AND   tq.`retour`           is null
AND   tq.`ul_id`            = :ul_id
";

    $parameters = [
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId
    ];

    return $this->executeQueryForUpdate($sql, $parameters);
  }

  /**
   * Cancel tronc retour.
   * Update one tronc and set 'retour' to null if comptage is null as well
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param int $ulId the Id of the Unite Local
   * @param int $userId id of the user performing the operation
   * @return int the number of row updated
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function cancelRetour(TroncQueteurEntity $tq, int $ulId, int $userId):int
  {
    $sql = "
UPDATE `tronc_queteur`           tq
SET    `retour`             = null,
       `last_update`        = NOW(),
       `last_update_user_id`= :userId            
WHERE tq.`id`               = :id
AND   tq.`comptage`         is null
AND   tq.`ul_id`            = :ul_id
";

    $parameters = [
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId
    ];
    return $this->executeQueryForUpdate($sql, $parameters);
  }

  /**
   * Update one tronc
   *
   * @param TroncQueteurEntity $tq The tronc to update
   * @param int $ulId the Id of the unite locale
   * @param int $userId id of the user performing the operation
   * @return Carbon the date of the udpate
   * @throws Exception if query fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function updateRetour(TroncQueteurEntity $tq, int $ulId, int $userId):Carbon
  {
    $sql = "
UPDATE `tronc_queteur`            tq
SET    `retour`               = :retour,
       `last_update`          = NOW(),
       `last_update_user_id`  = :userId,
       `notes_retour`         = :notes_retour            
WHERE tq.`id`                 = :id
AND   tq.ul_id                = :ul_id
";
    $currentDate = Carbon::now();
    $parameters = [
      "retour"            => $tq->retour->format('Y-m-d H:i:s'),
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId,
      "notes_retour"      => $tq->notes_retour
    ];

    $this->executeQueryForUpdate($sql, $parameters);

    return $currentDate;

  }

  /**
   * Update one tronc
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param boolean             $adminMode  If the user performing the action is administrator
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws Exception if the query fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function updateCoinsCount(TroncQueteurEntity $tq, bool $adminMode, int $ulId, int $userId):void
  {
    $this->logger->info("Saving coins for tronc",array("tronc"=> $tq, "adminMode" => $adminMode, "ulId"=>$ulId, "userId"=> $userId));

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

    $sql = "
UPDATE `tronc_queteur`            tq
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
 `don_creditcard`               = :don_creditcard,
 `don_cb_total_number`          = :don_cb_total_number         ,  
$comptage
 `last_update`                  = NOW(),
 `last_update_user_id`          = :userId,
 `notes_retour_comptage_pieces` = :notes_retour_comptage_pieces, 
 `coins_money_bag_id`           = :coins_money_bag_id,
 `bills_money_bag_id`           = :bills_money_bag_id,       
 `don_cheque_number`            = :don_cheque_number        

WHERE tq.`id`                   = :id
AND   tq.ul_id                  = :ul_id
";

    $parameters = [
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
      "don_cheque_number"             => $tq->don_cheque_number == null? 0:$tq->don_cheque_number,
      "don_creditcard"                => $tq->don_creditcard,
      "don_cb_total_number"           => $tq->don_cb_total_number
    ];

    $this->executeQueryForUpdate($sql, $parameters);
  }

  /**
   * Update one tronc as Admin for data like dates, and point_quete
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws Exception if the query fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function updateTroncQueteurAsAdmin(TroncQueteurEntity $tq, int $ulId, int $userId):void
  {

    $sql = "
UPDATE `tronc_queteur`             tq
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
AND   tq.ul_id         = :ul_id
";

    $parameters = [
      "depart_theorique"  => $tq->depart_theorique,
      "depart"            => $tq->depart,
      "retour"            => $tq->retour,
      "point_quete_id"    => $tq->point_quete_id,
      "deleted"           => $tq->deleted===true?"1":"0",
      "userId"            => $userId,
      "id"                => $tq->id,
      "ul_id"             => $ulId,
      "notes_update"      => $tq->notes_update
    ];

    $this->executeQueryForUpdate($sql, $parameters);
  }



  /**
   * Insert one TroncQueteur
   *
   * @param TroncQueteurEntity  $tq         The tronc to update
   * @param int                 $ulId       Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int                 $userId     id of the user performing the operation
   * @throws Exception if the query fails
   * @return PrepareTroncQueteurResponse Object containing the last insertedId or info about existing troncQueteur row already using the tronc_id (with depart or retour null and deleted=false)
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception if the tronc is already in use
   */
  public function insert(TroncQueteurEntity $tq, int $ulId, int $userId):PrepareTroncQueteurResponse
  {
    $checkTroncResult = $this->checkTroncNotAlreadyInUse($tq->tronc_id, $tq->queteur_id,$ulId);

    $prepareTroncQueteurResponse = new PrepareTroncQueteurResponse($checkTroncResult!= null, $checkTroncResult);

    if($checkTroncResult == null)
    {
      $parameters =
        [
          "ul_id"                 => $ulId,
          "queteur_id"            => $tq->queteur_id,
          "point_quete_id"        => $tq->point_quete_id,
          "tronc_id"              => $tq->tronc_id,
          "userId"                => $userId,
          "notes_depart_theorique"=> $tq->notes_depart_theorique
        ];

      $departTheoriqueQuery = null;

      if($tq->preparationAndDepart)
      {//if the user click on "Préparation et Départ", then depart_theorique is override by current date
        $departTheoriqueQuery ="  NOW(),";
      }
      else
      {
        $departTheoriqueQuery ="  :depart_theorique,";
        $parameters["depart_theorique"] = $tq->depart_theorique->format('Y-m-d H:i:s');
      }

//in case of preparationAndDepart, an update after this insert is done.
      $sql = "
INSERT INTO `tronc_queteur`
( 
   `ul_id`,
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
  :ul_id,
  :queteur_id,
  :point_quete_id, 
  :tronc_id, 
$departTheoriqueQuery
  NOW(),
  :userId,
  :notes_depart_theorique
)
";
      $prepareTroncQueteurResponse->lastInsertId = $this->executeQueryForInsert($sql, $parameters, true);
    }

    return $prepareTroncQueteurResponse;
  }


  /****
   * when preparing the tronc, upon saving, this function is called to check if this tronc is not already associated
   * with a tronc_queteur row for which the dateDepart or dateRetour is null (which means it should be in use)
   * @param int $troncId the Id of the tronc that we want to check
   * @param int $queteurId the Id of the queteur that we want to check
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return TroncInUseEntity[] Array of TroncInUseEntity that contains informations of the existing use of the tronc
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function checkTroncNotAlreadyInUse(int $troncId, int $queteurId, int $ulId):?array
  {
    $sqlTronc = "
SELECT  
tq.id, 
tq.ul_id, 
tq.queteur_id,
tq.tronc_id,  
tq.depart_theorique, 
tq.depart, 
q.first_name, 
q.last_name, 
q.email, 
q.mobile, 
q.nivol,
'TRONC_IN_USE' as status
FROM  tronc_queteur tq,
      queteur        q
WHERE    q.ul_id      = :ul_id
AND     tq.deleted    = 0
AND     tq.tronc_id   = :tronc_id
AND     tq.queteur_id = q.id
AND    (tq.depart     is null OR 
        tq.retour     is null)
";


    $sqlQueteur = "
SELECT  
tq.id, 
tq.ul_id, 
tq.queteur_id,
tq.tronc_id,  
tq.depart_theorique, 
tq.depart, 
q.first_name, 
q.last_name, 
q.email, 
q.mobile, 
q.nivol,
'QUETEUR_ALREADY_HAS_A_TRONC' as status
FROM  tronc_queteur tq,
      queteur        q
WHERE    q.ul_id      = :ul_id
AND     tq.deleted    = 0
AND     tq.queteur_id = :queteur_id
AND     tq.queteur_id = q.id
AND    (tq.depart     is null OR 
        tq.retour     is null)
";

    $existingUseOfTronc = null;
    $parameters=[
      "tronc_id"  => $troncId,
      "ul_id"     => $ulId
    ];

    $array1 = $this->executeQueryForArray($sqlTronc, $parameters, function($row) {
      return new TroncInUseEntity($row, $this->logger);
    });

    $parameters2=[
      "queteur_id" => $queteurId,
      "ul_id"      => $ulId
    ];
    $array2 = $this->executeQueryForArray($sqlQueteur, $parameters2, function($row) {
      return new TroncInUseEntity($row, $this->logger);
    });

    if($array1 == null && $array2 == null)
    {
      return null; // it's ok, no use of tronc
    }
    else
    {
      return array_merge($array1, $array2);
    }
  }


  /***
   *  work with  checkTroncNotAlreadyInUse($troncId, $ulId).
   * If the use decide to delete the tronc_queteur row because the existing rows
   * represents a session of quete that has not been performed
   * @param int $troncId the Id of the tronc to be deleted
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $userId the ID of the user performing the action
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   *
   */
  public function deleteNonReturnedTroncQueteur(int $troncId, int $ulId, int $userId):void
  {

    //update the tronc to deleted=1, limiting the update to the rows of the UL and set who is deleting the row
    $sql ="
UPDATE            tronc_queteur tq
SET   deleted             = 1,
      last_update_user_id = :user_id
WHERE tq.tronc_id = :tronc_id
AND   tq.ul_id    = :ul_id
AND  (tq.depart IS NULL OR 
      tq.retour IS NULL)
";
    $parameters = [
      "tronc_id"        => $troncId,
      "ul_id"           => $ulId   ,
      "user_id"         => $userId
    ];
    $this->executeQueryForUpdate($sql, $parameters);
  }



  /**
   * Get all previous version of a tronc_queteur by its id
   *
   * @param int $id The ID of the tronc_quete for which we want the history
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return TroncQueteurEntity[]  list of tronc_queteur entity (history mode)
   * @throws Exception if tronc_queteur not found
   * @throws PDOException if the query fails to execute on the server
   */
  public function getTroncQueteurHistoryById(int $id, int $ulId):array
  {
    $sql = "
SELECT 
t.`id`                        ,
t.`ul_id`                     ,
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
`don_cb_total_number`         ,       
`don_cheque_number`         
FROM  `tronc_queteur_historique`  as t
WHERE  t.tronc_queteur_id = :tronc_queteur_id
AND    t.ul_id            = :ul_id
ORDER BY t.id DESC
";

    $parameters = ["tronc_queteur_id" => $id, "ul_id" => $ulId];
    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new TroncQueteurEntity($row, $this->logger);
    });
  }

  /**
   * Get all tronc_queteur for an UniteLocale (for the specified year) when extracting data from an UL
   *
   * @param int $ulId the id of the unite locale  (join with queteur table)
   * @param string $year extract for the specified year. If null, all years.
   * @return TroncQueteurEntity[]  The tronc
   * @throws Exception if some parsing fails
   * @throws PDOException if the query fails to execute on the server
   */
  public function getTroncsQueteurFromUL(int $ulId, ?string $year, ?int $queteurId=null):array
  {
    $parameters = ["ul_id" => $ulId];
    $yearSQL="";
    if($year != null)
    {
      $yearSQL="AND  YEAR(tq.depart) = :year";
      $parameters["year"] = $year;
    }
    $queteurSQL="";
    if($queteurId != null)
    {
      $queteurSQL="AND  tq.queteur_id = :queteur_id";
      $parameters["queteur_id"] = $queteurId;
    }

    $sql = "
select
tq.id,
tq.ul_id,
tq.queteur_id,
tq.point_quete_id,
tq.tronc_id,
tq.depart_theorique,
tq.depart,
tq.retour,
tq.comptage,
tq.last_update,
tq.last_update_user_id,
q.first_name,
q.last_name,
q.mobile,
tq.euro200,
tq.euro100,
tq.euro50,
tq.euro20,
tq.euro10,
tq.euro5,
tq.euro2,
tq.euro1,
tq.cents50,
tq.cents20,
tq.cents10,
tq.cents5,
tq.cents2,
tq.cent1,
(tq.euro2*2 +
tq.euro1*1 +
tq.cents50*0.5 +
tq.cents20*0.2 +
tq.cents10*0.1 +
tq.cents5*0.05 +
tq.cents2*0.02 +
tq.cent1*0.01 +
tq.euro5*5 +
tq.euro10*10 +
tq.euro20*20 +
tq.euro50*50 +
tq.euro100*100 +
tq.euro200*200 +
tq.euro500*500 +
tq.don_cheque +
tq.don_creditcard) as amount,
( tq.euro500 * 1.1 +
 tq.euro200 * 1.1 +
 tq.euro100 * 1 +
 tq.euro50 * 0.9 +
 tq.euro20 * 0.8 +
 tq.euro10 * 0.7 +
 tq.euro5 * 0.6 +
 tq.euro2 * 8.5 +
 tq.euro1 * 7.5 +
 tq.cents50 * 7.8 +
 tq.cents20 * 5.74 +
 tq.cents10 * 4.1 +
 tq.cents5 * 3.92 +
 tq.cents2 * 3.06 +
 tq.cent1 * 2.3) as weight,
(timestampdiff(second,depart, retour))/3600 as time_spent_in_hours,
tq.deleted,
tq.don_creditcard,
tq.don_cheque,
tq.coins_money_bag_id,
tq.bills_money_bag_id
from tronc_queteur as tq,
        queteur           as q
WHERE tq.queteur_id = q.id
AND    q.ul_id      = :ul_id
$yearSQL
$queteurSQL
order by tq.id asc

";
    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new TroncQueteurEntity($row, $this->logger);
    });
  }
}
