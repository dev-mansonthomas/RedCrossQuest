<?php
namespace RedCrossQuest\DBService;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;


include_once("../../src/DBService/DBService.php");

use RedCrossQuest\Entity\UserEntity;

class UserDBService extends DBService
{

  /***
   * This function is used by the authenticate method, to get the user info from its nivol.
   * Can't be restricted by ULID since the UL is not known.
   *
   * @param $nivol the Nivol passed at login
   * @return an instance of UserEntity, null if nothing is found
   */
  public function getUserInfoWithNivol($nivol)
  {
    $sql = "
SELECT id, queteur_id, password, role, nb_of_failure, last_failure_login_date, last_successful_login_date 
FROM   users
WHERE  upper(nivol) = upper(?)
AND    active = 1
LIMIT 1
";

    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute([$nivol]);

    $this->logger->addInfo( "queryResult=$queryResult, $nivol, ".$stmt->rowCount());
    
    if($queryResult && $stmt->rowCount() == 1)
    {
      $result = new UserEntity($stmt->fetch());
      $stmt->closeCursor();
      return $result;
    }
  }


  /**
   * used in Password reset process
   * get user info for UUID if the init_passwd_date is after current time.
   *
   */
  public function getUserInfoWithUUID($uuid)
  {
    $sql = "
SELECT id, queteur_id, password, role, nb_of_failure, last_failure_login_date, last_successful_login_date 
FROM   users
WHERE  upper(init_passwd_uuid) = upper(:uuid)
AND    init_passwd_date > NOW()
AND    active = 1
LIMIT 1
";

    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute(["uuid" => $uuid]);

    $this->logger->addInfo( "queryResult=$queryResult, $uuid, ".$stmt->rowCount());

    if($queryResult && $stmt->rowCount() == 1)
    {
      $result = new UserEntity($stmt->fetch());
      $stmt->closeCursor();
      return $result;
    }
  }



  public function sendInit($username)
  {
    $uuid = Uuid::uuid4();

    $sql = "
UPDATE  `users`
SET     init_passwd_uuid  = :uuid,
        init_passwd_date  = DATE_ADD(NOW(), INTERVAL 1 HOUR)
WHERE   nivol             = :nivol
";

    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute(
      [
        "nivol" => $username,
        "uuid" => $uuid
      ]
    );


    if($queryResult && $stmt->rowCount() == 1)
    {
      return $uuid;
    }
  }

  public function resetPassword($uuid, $password)
  {
    $sql = "
UPDATE  `users`
SET     init_passwd_uuid  = null,
        init_passwd_date  = null,
        password          = :password
WHERE   upper(init_passwd_uuid) = upper(:uuid)
AND    init_passwd_date > NOW()
AND    active = 1
";

    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute(
      [
        "uuid"     => $uuid,
        "password" => password_hash($password, PASSWORD_DEFAULT)
      ]
    );


    if($queryResult && $stmt->rowCount() == 1)
    {
      return true;
    }
    return false;
  }
}
