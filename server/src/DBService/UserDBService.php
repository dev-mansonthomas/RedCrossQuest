<?php
namespace RedCrossQuest\DBService;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;


include_once("../../src/DBService/DBService.php");

use RedCrossQuest\Entity\UserEntity;

class UserDBService extends DBService
{


  /**
   * Insert one user for a queteur.
   *
   * @param $nivol : Nivol of the user
   * @param $queteurId : queteurId of the user
   * @return int the primary key of the new user
   */
  public function insert($nivol, $queteurId)
  {
    $sql = "
INSERT INTO `users`
(
`nivol`,
`queteur_id`,
`password`,
`role`,
`created`,
`updated`,
`active`,
`last_failure_login_date`,
`nb_of_failure`,
`last_successful_login_date`,
`init_passwd_uuid`,
`init_passwd_date`)
VALUES
(
:nivol,
:queteur_id,
'',
1,
NOW(),
NOW(),
1,
NULL,
0,
NULL,
NULL,
NULL
)
";

    $stmt = $this->db->prepare($sql);

    $this->db->beginTransaction();
    $result = $stmt->execute([
      "nivol"       => ltrim($nivol, '0'),
      "queteur_id"  => $queteurId
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];
    $this->logger->info('$lastInsertId:', [$lastInsertId]);

    $stmt->closeCursor();
    $this->db->commit();
    return $lastInsertId;
  }







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


  /***
   * This function is used by queteurEditForm, where the info from the user is retrieved from the queteurId
   *
   * @param $queteurId the Id of the queteur from which we want to retrieve the user
   * @param $ulId the id of the UL from the connected user, to check that he retrieves info from his UL only
   * @param $roleId the roleId of the connected user, to override UL Limitation for superadmin
   * @return an instance of UserEntity, null if nothing is found
   */
  public function getUserInfoWithQueteurId($queteurId, $ulId, $roleId)
  {
    $sql = "
SELECT u.id, u.queteur_id, LENGTH(u.password) >1 as password_defined, u.role, 
       u.nb_of_failure, u.last_failure_login_date, u.last_successful_login_date,
       u.init_passwd_date, u.active, u.created, u.updated
FROM   users u, queteur q
WHERE  u.queteur_id = :queteur_id
AND    q.id         = u.queteur_id
".($roleId!=9?"AND    q.ul_id      = :ul_id":"")."
LIMIT 1
";

    $parameters = null;
    if($roleId!=9)
    {
      $parameters =
        [
          "queteur_id"=>$queteurId,
          "ul_id"     =>$ulId
        ];
    }
    else
    {
      $parameters =
        [
          "queteur_id"=>$queteurId
        ];
    }

    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute($parameters);

    $this->logger->addInfo( "queryResult=$queryResult, queteurId=$queteurId, ulId=$ulId, count=".$stmt->rowCount());

    if($queryResult && $stmt->rowCount() == 1)
    {
      $result = new UserEntity($stmt->fetch());
      $stmt->closeCursor();
      return $result;
    }
  }


  /***
   * This function is used by queteurEditForm, where the info from the user is retrieved from the queteurId
   *
   * @param $userId the Id of the user
   * @param $ulId the id of the UL from the connected user, to check that he retrieves info from his UL only
   * @param $roleId the roleId of the connected user, to override UL Limitation for superadmin
   * @return an instance of UserEntity, null if nothing is found
   */
  public function getUserInfoWithUserId($userId, $ulId, $roleId)
  {
    $sql = "
SELECT u.id, u.queteur_id, LENGTH(u.password) >1 as password_defined, u.role, 
       u.nb_of_failure, u.last_failure_login_date, u.last_successful_login_date,
       u.init_passwd_date, u.active, u.created, u.updated
FROM   users u, queteur q
WHERE  u.id = :id
AND    q.id = u.queteur_id
".($roleId!=9?"AND    q.ul_id      = :ul_id":"")."
LIMIT 1
";

    $parameters = null;
    if($roleId!=9)
    {
      $parameters =
        [
          "id"    => $userId,
          "ul_id" => $ulId
        ];
    }
    else
    {
      $parameters =
        [
          "id"    => $userId
        ];
    }

    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute($parameters);

    $this->logger->addInfo( "queryResult=$queryResult, queteurId=$userId, ulId=$ulId, roleId=$roleId, count=".$stmt->rowCount());

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


  /**
   * update last successful login date and reset the count of failed login

   */
  public function registerSuccessfulLogin($userId)
  {
    $sql = "
UPDATE  `users`
SET     last_successful_login_date  = NOW(),
        nb_of_failure               = 0
WHERE   id = :id
";
    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute(
      [
        "id"     => $userId
      ]
    );

    if($queryResult && $stmt->rowCount() == 1)
    {
      return true;
    }
    return false;
  }

  /**
   * increment the failed login counter and update the last failed login date
   */
  public function registerFailedLogin($userId)
  {
    $sql = "
UPDATE  `users`
SET     last_failure_login_date  = NOW(),
        nb_of_failure            = nb_of_failure + 1
WHERE   id = :id
";
    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute(
      [
        "id"     => $userId
      ]
    );

    if($queryResult && $stmt->rowCount() == 1)
    {
      return true;
    }
    return false;
  }


  public function updateActiveAndRole(UserEntity $userEntity)
  {
    $sql = "
UPDATE  `users`
SET     active  = :active,
        role    = :role
WHERE   id      = :id
";
    $stmt = $this->db->prepare($sql);
    $queryResult = $stmt->execute(
      [
        "id"     => $userEntity->id,
        "active" => $userEntity->active,
        "role" => $userEntity->role
      ]
    );

    if($queryResult && $stmt->rowCount() == 1)
    {
      return true;
    }
    return false;
  }

}
