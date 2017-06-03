<?php

namespace RedCrossQuest\DBService;

use Ramsey\Uuid\Uuid;

use DateTime;
use DateInterval;


class SpotfireAccessDBService extends DBService
{
  /**
   * Insert a Token to access Spotfire Dashboards
   *
   * @param int $userId The ID of the connected user (should be taken from DecodedJWT)
   * @param int $ulId The ID of the Unite Locale of the connected user (should be taken from DecodedJWT)
   * @param int $tokenTTL TimeToLive in Hours of the Token
   * @return DateTime the date right after insertion, so that the frontend can know how long to wait for the next update of Spotfire.
   */
  public function grantAccess(int $userId, int $ulId, int $tokenTTL)
  {
    $token = Uuid::uuid4();

    //clean up previous access
    $deleteSQL="
    DELETE FROM spotfire_access
    WHERE id = :id
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
        "token_expiration"  => $tokenExpiryDate->format("Y-m-d"),
        "ul_id"             => $ulId,
        "user_id"           => $userId
      ]
    );

    $stmt->closeCursor();

    return (object)["creationTime"=>new DateTime(), "token"=>$token];
  }

}
