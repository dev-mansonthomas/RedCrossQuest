<?php

namespace RedCrossQuest\DBService;

use Ramsey\Uuid\Uuid;

use DateTime;
use DateInterval;
use Carbon\Carbon;
use RedCrossQuest\Entity\SpotfireAccessEntity;
use PDOException;


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
   */
  public function grantAccess(int $userId, int $ulId, int $tokenTTL)
  {
    $token = Uuid::uuid4();

    $currentValidToken = $this->getValidToken($userId, $ulId);

    if($currentValidToken != null)
    {//A valid Token exist, we don't overwrite it. (Otherwise the spotfire access keeps beeing disconnected)

     // $this->logger->info('spotfireAccess Token:', [$currentValidToken->token]);

      return (object)["token"=>$currentValidToken->token];
    }



    //clean up previous access
    $deleteSQL="
    DELETE FROM spotfire_access
    WHERE user_id = :user_id
    ";
    $stmt = $this->db->prepare($deleteSQL);
    $stmt->execute(["user_id"=> $userId ]);

    $stmt->closeCursor();

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


    $stmt = $this->db->prepare($sql);
    $stmt->execute(
      [
        "token"             => $token,
        "token_expiration"  => $tokenExpiryDate->format("Y-m-d H:i:s"),
        "ul_id"             => (int)$ulId,
        "user_id"           => (int)$userId
      ]
    );

    $stmt->closeCursor();

    $currentDate = new Carbon();
    $currentDate->setTimezone("Europe/Paris");

    return (object)["creationTime"=>$currentDate, "token"=>$token];
  }



  /**
   * Try to get an existing valid token
   *
   * @param int $userId The ID of the connected user (should be taken from DecodedJWT)
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return SpotfireAccessEntity the info of the spotfire token
   * @throws PDOException if the query fails to execute on the server
   */
  public function getValidToken(int $userId, int $ulId)
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

    $stmt = $this->db->prepare($sql);

    $stmt->execute(
      [
          "userId" => $userId,
          "ulId"   => $ulId
      ]);

    if($stmt->rowCount() == 1)
    {
      $data = $stmt->fetch();
      $spotfireAccess = new SpotfireAccessEntity($data);
    }
    else
    {
      $spotfireAccess = null;
    }
    $stmt->closeCursor();
    return $spotfireAccess;
  }

}
