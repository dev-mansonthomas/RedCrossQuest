<?php

namespace RedCrossQuest\DBService;

use Carbon\Carbon;
use DateInterval;
use DateTime;
use Exception;
use PDOException;
use Ramsey\Uuid\Uuid;
use RedCrossQuest\Entity\SpotfireAccessEntity;


class SpotfireAccessDBService extends DBService
{
  /**
   * Insert a Token to access Spotfire Dashboards
   *
   * @param int $userId The ID of the connected user (should be taken from DecodedJWT)
   * @param int $ulId The ID of the Unite Locale of the connected user (should be taken from DecodedJWT)
   * @param int $tokenTTL TimeToLive in Hours of the Token
   * @return Object the date right after insertion, so that the frontend can know how long to wait for the next update of Spotfire.
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function grantAccess(int $userId, int $ulId, int $tokenTTL):object 
  {
    $token = Uuid::uuid4();

    $currentValidToken = $this->getValidToken($userId, $ulId);

    if($currentValidToken != null)
    {//A valid Token exist, we don't overwrite it. (Otherwise the spotfire access keeps being disconnected)

     // $this->logger->info('spotfireAccess Token:', [$currentValidToken->token]);

      return (object)["token"=>$currentValidToken->token, "token_expiration"=>$currentValidToken->token_expiration];
    }



    //clean up previous access
    $deleteSQL="
    DELETE FROM spotfire_access
    WHERE user_id = :user_id
    AND   ul_id   = :ul_id
    ";

    $parameters = ["user_id"=> $userId,  "ul_id"=> $ulId];

    $this->executeQueryForUpdate($deleteSQL, $parameters);


    //create a new one
    $sql = "
INSERT INTO `spotfire_access`
(
  `token`,
  `token_expiration`,
  `ul_id`,
  `user_id`
)
VALUES  
(
  :token,
  :token_expiration,
  :ul_id,
  :user_id
)
";
    $tokenExpiryDate =  new DateTime();
    $tokenExpiryDate->add(new DateInterval("PT".$tokenTTL."H"));

    $parameters2=[
      "token"             => $token,
      "token_expiration"  => $tokenExpiryDate->format("Y-m-d H:i:s"),
      "ul_id"             => (int)$ulId,
      "user_id"           => (int)$userId
    ];

    $this->executeQueryForInsert($sql, $parameters2, false);

    $currentDate = Carbon::now();
    $currentDate->setTimezone("Europe/Paris");

    return $this->getValidToken($userId, $ulId);
  }



  /**
   * Try to get an existing valid token
   *
   * @param int $userId The ID of the connected user (should be taken from DecodedJWT)
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return SpotfireAccessEntity the info of the spotfire token
   * @throws PDOException if the query fails to execute on the server
   * @throws Exception in other situations, possibly : parsing error in the entity
   */
  public function getValidToken(int $userId, int $ulId):?SpotfireAccessEntity
  {
    $sql = "
    SELECT  token, token_expiration
    FROM    spotfire_access
    WHERE   ul_id   = :ulId
    AND     user_id = :userId
    AND     token_expiration > NOW()
    ";
//+ INTERVAL 1 HOUR
//TODO need to check if we need an offset of 1 or 2 hours, for the timezone differences.

    $parameters = [
      "userId" => $userId,
      "ulId"   => $ulId
    ];

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return $this->executeQueryForObject($sql, $parameters, function($row) {
      return new SpotfireAccessEntity($row, $this->logger);
    }, false);
  }
}
