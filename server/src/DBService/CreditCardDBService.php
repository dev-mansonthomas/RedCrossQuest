<?php
namespace RedCrossQuest\DBService;

use DateInterval;
use DateTime;
use Exception;
use PDO;
use PDOException;
use RedCrossQuest\Entity\CreditCardEntity;
use RedCrossQuest\Entity\DailyStatsBeforeRCQEntity;
use RedCrossQuest\Service\Logger;

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
  public function getCreditCardEntriesForTroncQueteur(int $troncQueteurId, int $ulId):array
  {

    $parameters = ["tronc_queteur_id"=>$troncQueteurId, "ul_id" => $ulId];

    $sql = "
SELECT  c.`id`,
        c.`tronc_queteur_id`,
        c.`ul_id`,
        c.`quantity`,
        c.`amount`
FROM `credit_card` AS c
WHERE c.tronc_queteur_id = :tronc_queteur_id
AND   c.ul_id            = :ul_id
ORDER BY c.amount ASC
";

    return $this->executeQueryForArray($sql, $parameters, function($row) {
      return new CreditCardEntity($row, $this->logger);
    });
  }

  /**
   * Create or update CreditCard donation records
   * An update is done if the record has an id, otherwise it's an insert
   * 
   * @param CreditCardEntity[] $creditCardEntities array of credit cards donations
   * @param int    $troncQueteurId  Id of the parent TroncQueteur
   * @param int $ulId The ID of the Unite Locale
   * @throws Exception if some parsing error occurs
   */
  public function createOrUpdateOrDeleteCreditCardDonation(array $creditCardEntities, int    $troncQueteurId, int $ulId):void
  {
    foreach ($creditCardEntities as $creditCardEntity)
    {
      if($creditCardEntity->id > 0)
      {
        if($creditCardEntity->delete === true)
        {
          $this->delete($creditCardEntity, $ulId);
        }
        else
        {
          $this->update($creditCardEntity, $ulId);
        }

      }
      else
      {
        $this->insertCreditCard($creditCardEntity, $troncQueteurId, $ulId);
      }
    }
  }


  /**
   * update a CreditCard donation
   * @param CreditCardEntity $creditCardEntity info about the CreditCard donation
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function update(CreditCardEntity $creditCardEntity, int $ulId):void
  {
    $sql ="
update  `credit_card`
set     `amount`        = :amount,
        `quantity`      = :quantity
where   `id`      = :id
AND     `ul_id`   = :ulId   
";
    $parameters = [
      "amount"        => $creditCardEntity->amount,
      "quantity"      => $creditCardEntity->quantity,
      "id"            => $creditCardEntity->id,
      "ulId"          => $ulId
    ];
    
    $this->executeQueryForUpdate($sql, $parameters);
  }


  /**
   * delete a CreditCard donation
   * @param CreditCardEntity $creditCardEntity info about the CreditCard donation
   * @param int $ulId Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception
   */
  public function delete(CreditCardEntity $creditCardEntity, int $ulId):void
  {
    $sql ="
DELETE  FROM `credit_card`
WHERE   `id`      = :id
AND     `ul_id`   = :ulId   
";
    $parameters = [
      "id"            => $creditCardEntity->id,
      "ulId"          => $ulId
    ];

    $this->executeQueryForUpdate($sql, $parameters);
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
