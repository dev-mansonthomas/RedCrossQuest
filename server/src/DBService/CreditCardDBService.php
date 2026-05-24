<?php
namespace RedCrossQuest\DBService;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use RedCrossQuest\Entity\CreditCardEntity;
use RedCrossQuest\Entity\DailyStatsBeforeRCQEntity;
use RedCrossQuest\Service\Logger;
use Throwable;

class CreditCardDBService extends DBService
{
  public function __construct(PDO $db, Logger $logger)
  {
    parent::__construct($db,$logger);
  }


  /**
   * Get CreditCard details of a TroncQueteur

   * @param int $troncQueteurId The ID of the TroncQueteur
   * @param int $ulId The ID of the Unite Locale
   * @return CreditCardEntity[]  The list of CreditCard
   * @throws Exception if some parsing error occurs
   */
  public function getCreditCardEntriesForTroncQueteur(int $troncQueteurId, int $ulId, int $roleId):array
  {

    $parameters = ["tronc_queteur_id"=>$troncQueteurId];

    $sql = "
SELECT  c.`id`,
        c.`tronc_queteur_id`,
        c.`ul_id`,
        c.`quantity`,
        c.`amount`
FROM `credit_card` AS c
WHERE c.tronc_queteur_id = :tronc_queteur_id
";

    if($roleId != 9)
    {
      $sql .= "
AND   c.ul_id            = :ul_id      
";
      $parameters["ul_id"] = $ulId;
    }

    $sql .="
ORDER BY c.amount ASC
";


    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new CreditCardEntity($row, $this->logger);
    });
  }

  /**
   * Persist the CreditCard donation rows attached to a TroncQueteur.
   *
   * Strategy: DELETE all existing rows for this tronc_queteur, then INSERT the cleaned
   * input set, the whole sequence wrapped in a transaction. credit_card row IDs are not
   * externally referenced (no FK pointing at credit_card.id), so losing the historical IDs
   * is acceptable. This makes persistence idempotent w.r.t. the payload and naturally
   * cleans up any pre-existing duplicates on every save (defense against the legacy
   * duplication bug observed on tronc_queteur_id=36070 and others).
   *
   * Pre-flight validation runs first and may throw InvalidArgumentException, surfaced as
   * a 400 to the client by SaveCoinsOnTroncQueteur.
   *
   * @param CreditCardEntity[] $creditCardEntities array of credit card donation rows
   * @param int                $troncQueteurId     Id of the parent TroncQueteur
   * @param int                $ulId               Id of the Unite Locale
   * @throws InvalidArgumentException if the payload has the same amount with different quantities
   * @throws Exception                on DB error
   */
  public function createOrUpdateOrDeleteCreditCardDonation(array $creditCardEntities, int    $troncQueteurId, int $ulId):void
  {
    //S1 pre-flight: dedup identical (amount,quantity) silently (log ERROR with full details),
    //throw InvalidArgumentException if same amount has conflicting quantities (-> 400).
    $cleaned = $this->dedupAndValidateCBEntities($creditCardEntities, $troncQueteurId, $ulId);

    //S2 persistence: DELETE bulk + INSERT all in a transaction.
    //inTransaction() check mirrors the pattern used by executeQueryForInsert(): if a caller
    //already opened a transaction (currently none for this path, but future-proof), we
    //participate in it rather than nesting (MySQL does not support nested transactions).
    $transactionStartedHere = false;
    if(!$this->db->inTransaction())
    {
      $this->db->beginTransaction();
      $transactionStartedHere = true;
    }
    try
    {
      $this->deleteAllForTroncQueteur($troncQueteurId, $ulId);
      foreach($cleaned as $creditCardEntity)
      {
        $this->insertCreditCard($creditCardEntity, $troncQueteurId, $ulId);
      }
      if($transactionStartedHere)
      {
        $this->db->commit();
      }
    }
    catch(Throwable $e)
    {
      if($transactionStartedHere && $this->db->inTransaction())
      {
        $this->db->rollBack();
      }
      throw $e;
    }
  }


  /**
   * Pre-flight check on the incoming CB payload.
   *
   * Filters out rows marked for delete and rows with null/0 quantity (preset slots left
   * empty by the user). Groups the remaining rows by amount:
   * - 1 row per amount    -> kept as-is
   * - N rows, same quantity      -> silent dedup, ERROR log with full debug context
   * - N rows, different quantity -> InvalidArgumentException (client sees 400)
   *
   * The frontend already guards both cases (load-time dedup + hasCBDetailsForDuplicateAmount
   * blocking the save), but server-side validation is required to defend against stale
   * clients, replayed requests, and direct API calls.
   *
   * @param CreditCardEntity[] $creditCardEntities
   * @param int                $troncQueteurId
   * @param int                $ulId
   * @return CreditCardEntity[] entities ready to insert (filtered + deduped)
   * @throws InvalidArgumentException on amount/quantity conflict
   */
  private function dedupAndValidateCBEntities(array $creditCardEntities, int $troncQueteurId, int $ulId):array
  {
    $byAmount = [];
    foreach($creditCardEntities as $e)
    {
      if($e->delete === true)                                continue;
      if($e->quantity === null || $e->quantity <= 0)         continue;
      $amountKey = (string)$e->amount;
      if(!isset($byAmount[$amountKey])) $byAmount[$amountKey] = [];
      $byAmount[$amountKey][] = $e;
    }

    $cleaned = [];
    foreach($byAmount as $amountKey => $entities)
    {
      if(count($entities) === 1)
      {
        $cleaned[] = $entities[0];
        continue;
      }

      $quantitiesSet = [];
      foreach($entities as $e) $quantitiesSet[(string)$e->quantity] = true;

      if(count($quantitiesSet) === 1)
      {
        $this->logger->error("CB pre-flight: silently merged duplicate (amount, quantity) rows in payload", [
          "tronc_queteur_id" => $troncQueteurId,
          "ul_id"            => $ulId,
          "amount"           => $entities[0]->amount,
          "quantity"         => $entities[0]->quantity,
          "duplicateCount"   => count($entities),
          "entities"         => $entities,
        ]);
        $cleaned[] = $entities[0];
      }
      else
      {
        $this->logger->error("CB pre-flight: conflicting quantities for the same amount in payload", [
          "tronc_queteur_id" => $troncQueteurId,
          "ul_id"            => $ulId,
          "amount"           => $entities[0]->amount,
          "quantities"       => array_keys($quantitiesSet),
          "entities"         => $entities,
        ]);
        throw new InvalidArgumentException(
          "Payload CB contient des lignes en conflit pour le montant ".$entities[0]->amount.
          " avec des quantites differentes (".implode(",", array_keys($quantitiesSet)).
          "). tronc_queteur_id=".$troncQueteurId
        );
      }
    }

    return $cleaned;
  }


  /**
   * Delete all credit_card rows for a given tronc_queteur (scoped to the UL of the caller).
   * Used by the DELETE+INSERT persistence strategy of createOrUpdateOrDeleteCreditCardDonation.
   *
   * @param int $troncQueteurId
   * @param int $ulId
   * @return int number of deleted rows
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  private function deleteAllForTroncQueteur(int $troncQueteurId, int $ulId):int
  {
    $sql = "
DELETE FROM `credit_card`
WHERE `tronc_queteur_id` = :tronc_queteur_id
AND   `ul_id`            = :ul_id
";
    $parameters = [
      "tronc_queteur_id" => $troncQueteurId,
      "ul_id"            => $ulId,
    ];

    return $this->executeQueryForUpdate($sql, $parameters);
  }

  /**
   * Insert a credit card detail
   *
   * @param CreditCardEntity $creditCardEntity details of the creditCardEntity
   * @param int    $troncQueteurId  Id of the parent TroncQueteur
   * @param int    $ulId  Id of the UL for which we create the data
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception if something else fails
   */
  public function insertCreditCard(CreditCardEntity $creditCardEntity, int $troncQueteurId, int $ulId):void
  {
    $sql = "
INSERT INTO `credit_card`
(
  `tronc_queteur_id`, 
  `ul_id`,
  `quantity`,
  `amount`
)
VALUES
(
  :tronc_queteur_id,  
  :ul_id,           
  :quantity,
  :amount
)
";
    $stmt         = $this->db->prepare($sql);

    $stmt->execute([
      "tronc_queteur_id"=> $troncQueteurId,
      "ul_id"           => $ulId,
      "quantity"        => $creditCardEntity->quantity,
      "amount"          => $creditCardEntity->amount
    ]);

    $stmt->closeCursor();
  }
  
}
