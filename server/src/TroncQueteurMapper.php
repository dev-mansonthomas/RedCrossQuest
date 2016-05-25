<?php
namespace RedCrossQuest;


use Carbon\Carbon;

class TroncQueteurMapper extends Mapper
{

  /**
   * Get the last tronc_queteur for the tronc_id
   *
   * @param int $tronc_id The ID of the tronc
   * @return TroncQueteurEntity  The tronc
   */
  public function getLastTroncQueteurByTroncId($tronc_id)
  {
    $sql = "
SELECT 
 `id`                ,
 `queteur_id`        ,
 `point_quete_id`    ,
 `tronc_id`          ,
 `depart_theorique`  ,
 `depart`            ,
 `retour`            ,
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
 `notes`
FROM  `tronc_queteur` as t
WHERE  t.tronc_id = :tronc_id
ORDER BY id DESC
LIMIT 1
";

    $stmt = $this->db->prepare($sql);

    $result = $stmt->execute(["tronc_id" => $tronc_id]);

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
     * Get one tronc_queteur by its ID
     *
     * @param int $tronc_queteur_id The ID of the tronc_quete row
     * @return TroncQueteurEntity  The tronc
     */
    public function getTroncQueteurById($id)
    {
      $sql = "
SELECT 
 `id`                ,
 `queteur_id`        ,
 `point_quete_id`    ,
 `tronc_id`          ,
 `depart_theorique`  ,
 `depart`            ,
 `retour`            ,
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
 `notes`
FROM  `tronc_queteur` as t
WHERE  t.id = :id
";

      $stmt = $this->db->prepare($sql);

      $result = $stmt->execute(["id" => $id]);

      if($result && $stmt->rowCount() == 1 )
      {
        $tronc = new TroncQueteurEntity($stmt->fetch());
        $stmt->closeCursor();
        return $tronc;
      }
      else
      {
        throw new \Exception("Tronc Queteur with ID:'".$id . "' not found");
      }
    }


  /**
   * Update one tronc
   *
   * @param TroncQueteurEntity $tronc The tronc to update
   */
    public function updateCoinsCount(TroncQueteurEntity $tq)
    {
      $sql = "
UPDATE `tronc_queteur`
SET
 `euro500`           = :euro500,           
 `euro200`           = :euro200,           
 `euro100`           = :euro100,           
 `euro50`            = :euro50 ,           
 `euro20`            = :euro20 ,           
 `euro10`            = :euro10 ,           
 `euro5`             = :euro5  ,           
 `euro2`             = :euro2  ,           
 `euro1`             = :euro1  ,           
 `cents50`           = :cents50,           
 `cents20`           = :cents20,           
 `cents10`           = :cents10,           
 `cents5`            = :cents5 ,           
 `cents2`            = :cents2 ,           
 `cent1`             = :cent1  ,           
 `foreign_coins`     = :foreign_coins,     
 `foreign_banknote`  = :foreign_banknote,  
 `notes`             = :notes             
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
        "id"                => $tq->id
      ]);

      $this->logger->warning($stmt->rowCount());
      $stmt->closeCursor();

      if(!$result) {
          throw new Exception("could not save record");
      }
    }



  /**
   * Insert one TroncQueteur
   *
   * @param TroncQueteurEntity $tronc The tronc to update
   * @return int the primary key of the new tronc
   */
  public function insert(TroncQueteurEntity $tq)
  {


    $checkTroncResult = $this->checkTroncNotAlreadyInUse($tq->tronc_id);

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
   `depart_theorique` 
)
VALUES
(
  :queteur_id,
  :point_quete_id, 
  :tronc_id, 
  :depart_theorique
)
";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $result = $stmt->execute([
      "queteur_id"        => $tq->queteur_id,
      "point_quete_id"    => $tq->point_quete_id,
      "tronc_id"          => $tq->tronc_id,
      "depart_theorique"  => $tq->depart_theorique->format('Y-m-d H:i:s')
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



  public function checkTroncNotAlreadyInUse($troncId)
  {
    $sql = "
SELECT 	tq.id, tq.queteur_id, tq.depart_theorique, tq.depart, q.first_name, q.last_name, q.email, q.mobile, q.nivol
FROM 	tronc_queteur tq,
      queteur q
WHERE 	tq.tronc_id=:tronc_id
AND     tq.queteur_id = q.id
AND    (tq.depart is null OR 
        tq.retour is null)
";

    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      "tronc_id"        => $troncId
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




  public function deleteNonReturnedTroncQueteur($troncId)
  {
    $sql ="
DELETE FROM tronc_queteur
WHERE       tronc_id=:tronc_id
AND         (depart IS NULL OR 
             retour IS NULL) 
";

    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
      "tronc_id"        => $troncId
    ]);

    $rowCount = $stmt->rowCount();

    $this->logger->info("deleted ".$rowCount." non returned tronc_queteur with tronc_id='".$troncId."'");



  }

}
