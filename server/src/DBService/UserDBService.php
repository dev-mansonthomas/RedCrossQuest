<?php
namespace RedCrossQuest\DBService;

use Exception;
use PDOException;
use Ramsey\Uuid\Uuid;
use RedCrossQuest\Entity\UserEntity;
use RedCrossQuest\Exception\UserAlreadyExistsException;

class UserDBService extends DBService
{


  /**
   * Check if at least one >active< user exist on the system with the passed nivol. (a NIVOL currently disabled that the admin tries to activate)
   * If so, it throws an exception with the data of the users.
   * if not, do nothing.
   * @param string $nivol nivol to check
   * @throws UserAlreadyExistsException if at least one active user exist on the whole system
   */
  public function checkExistingUserWithNivol(string $nivol):void
  {
    $sql = "
select  u.`id`        ,
        u.`nivol`     ,
        u.`queteur_id`,
        u.`role`      ,
        u.`created`   ,
        u.`updated`   ,
        u.`active`    ,
        q.`first_name`,
        q.`last_name` ,
        q.`email`     ,
        q.`secteur`   ,
        q.`nivol`     ,
        q.`mobile`    ,
        q.`created`   ,
        q.`updated`   ,
        q.`ul_id`     ,
        q.`man`       ,
        q.`active`     
from users u, queteur q
where u.nivol like :nivol
AND u.active = 1
AND u.queteur_id = q.id
";

    $parameters = ["nivol"=>$nivol];

    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = $row;
    }

    $stmt->closeCursor();

    if($i>0)
    {
      $exception = new UserAlreadyExistsException($i. " utilisateurs actifs RCQ existent déjà avec ce nivol: '$nivol'.\nVeuillez contacter l'administrateur RCQ sur slack ou support.redcrossquest@croix-rouge.fr");
      $exception->users = $results;
      throw $exception;
    }
  }



  /**
   * Insert one user for a queteur.
   *
   * @param string $nivol : Nivol of the user
   * @param int    $queteurId : queteurId of the user
   * @return int the primary key of the new user
   * @throws PDOException if the query fails to execute on the server
   * @throws UserAlreadyExistsException if at least one active user exist on the whole system
   */
  public function insert(string $nivol, int $queteurId):int
  {

    $this->checkExistingUserWithNivol($nivol);

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
    $stmt->execute([
      "nivol"       => ltrim($nivol, '0'),
      "queteur_id"  => $queteurId
    ]);

    $stmt->closeCursor();

    $stmt = $this->db->query("select last_insert_id()");
    $row = $stmt->fetch();

    $lastInsertId = $row['last_insert_id()'];
    //$this->logger->info('$lastInsertId:', [$lastInsertId]);

    $stmt->closeCursor();
    $this->db->commit();
    return $lastInsertId;
  }



  /***
   * This function is used by the firebase-authenticate route, to get the user info from its email.
   * Can't be restricted by ULID since the UL is not known at this point.
   *
   * @param string $email The email passed at login
   * @return UserEntity An instance of UserEntity, null if nothing is found
   * @throws Exception in case of incorrect number of rows updated
   * @throws PDOException if the query fails to execute on the server
   */
  public function getUserInfoWithEmail(string $email):UserEntity
  {
    $sql = "
SELECT u.id, u.queteur_id, u.password, u.role, u.nb_of_failure, u.last_failure_login_date, u.last_successful_login_date 
FROM   users u, queteur q
WHERE  upper(q.email) = upper(?)
AND    q.id = u.queteur_id
AND    u.active = 1
LIMIT 1
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$email]);

    $count = $stmt->rowCount();

    if($count == 1)
    {
      //temp var, because pass by reference
      $row = $stmt->fetch();
      $result = new UserEntity($row, $this->logger);
      $stmt->closeCursor();
      return $result;
    }
    else
    {
      $stmt->closeCursor();
      throw new Exception ("Update didn't update the correct number of rows($count) for nivol: $email");
    }
  }
  /***
   * This function is used by the authenticate method (prior to firebase), to get the user info from its nivol.
   * Can't be restricted by ULID since the UL is not known.
   *
   * @param string $nivol string The Nivol passed at login
   * @return UserEntity An instance of UserEntity, null if nothing is found
   * @throws Exception in case of incorrect number of rows updated
   * @throws PDOException if the query fails to execute on the server
   */
  public function getUserInfoWithNivol(string $nivol):UserEntity
  {
    $sql = "
SELECT id, queteur_id, password, role, nb_of_failure, last_failure_login_date, last_successful_login_date 
FROM   users
WHERE  upper(nivol) = upper(?)
AND    active = 1
LIMIT 1
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$nivol]);

    $count = $stmt->rowCount();

    if($count == 1)
    {
      //temp var, because pass by reference
      $row = $stmt->fetch();
      $result = new UserEntity($row, $this->logger);
      $stmt->closeCursor();
      return $result;
    }
    else
    {
      $stmt->closeCursor();
      throw new Exception ("Update didn't update the correct number of rows($count) for nivol: $nivol");
    }
  }





  /***
   * This function is used by queteurEditForm, where the info from the user is retrieved from the queteurId
   *
   * @param int $queteurId the Id of the queteur from which we want to retrieve the user
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId the roleId of the connected user, to override UL Limitation for superadmin
   * @return UserEntity an instance of UserEntity, null if nothing is found
   * @throws Exception in case of incorrect number of rows updated
   * @throws PDOException if the query fails to execute on the server
   */
  public function getUserInfoWithQueteurId(int $queteurId, int $ulId, int $roleId):?UserEntity
  {
    $limitQueryToUl="";
    if($roleId!=9)
    {
      $limitQueryToUl="AND    q.ul_id      = :ul_id";
    }

    $sql = "
SELECT u.id, u.queteur_id, LENGTH(u.password) >1 as password_defined, u.role, 
       u.nb_of_failure, u.last_failure_login_date, u.last_successful_login_date,
       u.init_passwd_date, u.active, u.created, u.updated, q.first_name, q.last_name
FROM   users u, queteur q
WHERE  u.queteur_id = :queteur_id
AND    q.id         = u.queteur_id
$limitQueryToUl
LIMIT 1
";

    $parameters = ["queteur_id"=>$queteurId];

    if($roleId!=9)
    {
      $parameters["ul_id"]=$ulId;
    }


    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    $count = $stmt->rowCount();

    if($count == 1)
    {
      //temp var, because pass by reference
      $row = $stmt->fetch();
      $result = new UserEntity($row, $this->logger);
      $stmt->closeCursor();
      return $result;
    }
    return null;

  }


  /***
   * This function is used by queteurEditForm, where the info from the user is retrieved from the queteurId
   * Can't fetch data from superuser
   *
   * @param int $userId the Id of the user
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @param int $roleId the roleId of the connected user, to override UL Limitation for superadmin
   * @return UserEntity an instance of UserEntity, null if nothing is found
   * @throws Exception In case a validation is failing while creating  the user entity.
   * @throws PDOException if the query fails to execute on the server
   */
  public function getUserInfoWithUserId(int $userId, int $ulId, int $roleId):?UserEntity
  {
    $limitQueryToUl="";
    if($roleId!=9)
    {
      $limitQueryToUl="AND    q.ul_id      = :ul_id";
    }

    $sql = "
SELECT u.id, u.queteur_id, LENGTH(u.password) >1 as password_defined, u.role, 
       u.nb_of_failure, u.last_failure_login_date, u.last_successful_login_date,
       u.init_passwd_date, u.active, u.created, u.updated, u.nivol, q.first_name, q.last_name
FROM   users u, queteur q
WHERE  u.id = :id
AND    q.id = u.queteur_id
$limitQueryToUl
LIMIT 1
";

    $parameters = ["id"    => $userId];

    if($roleId!=9)
    {
      $parameters["ul_id"]= $ulId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    //$this->logger->info( "queryResult=$queryResult, queteurId=$userId, ulId=$ulId, roleId=$roleId, count=".$stmt->rowCount());

    $count = $stmt->rowCount();

    if($count == 1)
    {
      //temp var, because pass by reference
      $row = $stmt->fetch();
      $result = new UserEntity($row, $this->logger);
      $stmt->closeCursor();
      return $result;
    }

    return null;

  }


  /**
   * used in Password reset process
   * get user info for UUID if the init_passwd_date is after current time.
   * @param string $uuid the UUID to retrieve the user info
   * @return USerEntity the info of the user
   * @throws Exception in case of incorrect number of rows updated
   * @throws PDOException if the query fails to execute on the server
   */
  public function getUserInfoWithUUID(string $uuid):UserEntity
  {
    $sql = "
SELECT id, queteur_id, password, role, nb_of_failure, last_failure_login_date, last_successful_login_date 
FROM   users
WHERE  upper(init_passwd_uuid) = upper(:uuid)
AND    init_passwd_date > NOW()
AND    active = 1
AND    role  != 9
LIMIT 1
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(["uuid" => $uuid]);

    //$this->logger->info( "queryResult=$queryResult, $uuid, ".$stmt->rowCount());

    $count = $stmt->rowCount();

    if($count == 1)
    {
      //temp var, because pass by reference
      $row = $stmt->fetch();
      $result = new UserEntity($row, $this->logger);
      $stmt->closeCursor();
      return $result;
    }
    else
    {
      $stmt->closeCursor();
      throw new Exception ("Update didn't update the correct number of rows($count) for '$uuid' ");
    }

  }



  /***
   * This function is used by dataExport
   *
   * @param int $ulId  Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return UserEntity[] array of users of  the UnitéLocale
   * @throws Exception in case of incorrect number of rows updated
   * @throws PDOException if the query fails to execute on the server
   */
  public function getULUsers(int $ulId):array
  {

    $sql = "
SELECT u.id, u.queteur_id, LENGTH(u.password) >1 as password_defined, u.role, 
       u.nb_of_failure, u.last_failure_login_date, u.last_successful_login_date,
       u.init_passwd_date, u.active, u.created, u.updated, q.first_name, q.last_name
FROM   users u, queteur q
WHERE  u.queteur_id = q.id
AND    q.ul_id      = :ul_id
LIMIT 1
";

    $parameters = ["ul_id"=>$ulId];

    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    $results = [];
    $i = 0;
    while ($row = $stmt->fetch())
    {
      $results[$i++] = new UserEntity($row, $this->logger);
    }

    $stmt->closeCursor();

    return $results;

  }

  /**
   * update the user with the init uuid (generated buy this method) and the time until the uuid is valid (now+one hour)
   *
   * @param string $username the nivol of the user who want to init its password
   * @param bool          $firstInit  If it's the first init, the TTL of the link is 48h, otherwise 4h
   * @return string the generated uuid
   * @throws Exception in case of incorrect number of rows updated
   * @throws PDOException if the query fails to execute on the server
   */
  public function sendInit(string $username, bool $firstInit = false):string
  {
    $uuid = Uuid::uuid4();

    if($firstInit)
      $mailTTL = 48;
    else
      $mailTTL = 4;

    $sql = "
UPDATE  `users`
SET     init_passwd_uuid  = :uuid,
        init_passwd_date  = DATE_ADD(NOW(), INTERVAL $mailTTL HOUR)
WHERE   nivol             = UPPER(:nivol)
AND     active            = 1
AND     role             != 9
";

    $this->logger->info("SendInit for password reset", ['username'=>$username, 'uuid'=>$uuid, 'mailTTL'=>$mailTTL]);

    $stmt = $this->db->prepare($sql);
    $stmt->execute(
      [
        "nivol" => $username,
        "uuid"  => $uuid
      ]
    );

    $count = $stmt->rowCount();
    $stmt->closeCursor();

    if($count == 1)
    {
      return $uuid;
    }
    
    throw new Exception ("Update didn't update the correct number of rows($count) for $username");
  }

  /**
   * Save the new password for a user, from the UUID
   * @param string $uuid the uuid that identifiy the user that update his password
   * @param string $password the new password( clear text), stores it as a hash
   * @return bool true if the query is successfull, false otherwise
   * @throws PDOException if the query fails to execute on the server
   */
  public function resetPassword(string $uuid, string $password):bool
  {
    $sql = "
UPDATE  `users`
SET     init_passwd_uuid  =  null,
        init_passwd_date  =  null,
        password          = :password
WHERE   upper(init_passwd_uuid) = upper(:uuid)
AND     init_passwd_date > NOW()
AND     active = 1
AND     role  != 9
";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(
      [
        "uuid"     => $uuid,
        "password" => password_hash($password, PASSWORD_DEFAULT)
      ]
    );

    if($stmt->rowCount() == 1)
    {
      $stmt->closeCursor();
      return true;
    }
    $stmt->closeCursor();
    return false;
  }


  /**
   * update last successful login date and reset the count of failed login
   * @param int $userId the id of the user that is connecting
   * @return bool true if query successful, false otherwise
   * @throws PDOException if the query fails to execute on the server
   */
  public function registerSuccessfulLogin(int $userId):bool
  {
    $sql = "
UPDATE  `users`
SET     last_successful_login_date  = NOW(),
        nb_of_failure               = 0
WHERE   id                          = :id
";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(
      [
        "id"     => $userId
      ]
    );

    if($stmt->rowCount() == 1)
    {
      return true;
    }
    return false;
  }

  /**
   * increment the failed login counter and update the last failed login date
   * @param int $userId the id of the user that is connecting
   * @return boolean true if query successful, false otherwise
   * @throws PDOException if the query fails to execute on the server
   */
  public function registerFailedLogin(int $userId):bool
  {
    $sql = "
UPDATE  `users`
SET     last_failure_login_date  = NOW(),
        nb_of_failure            = nb_of_failure + 1
WHERE   id                       = :id
";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(
      [
        "id"     => $userId
      ]
    );

    if($stmt->rowCount() == 1)
    {
      return true;
    }
    return false;
  }


  /**
   * this method update the 'active' and 'role' column of the user (for non super user)
   * @param UserEntity $userEntity the user info
   * @param int         $ulId   the UL ID  of the person performing the action
   * @param int         $roleId the RoleID of the person performing the action
   * @return bool true if query is successful, false otherwise
   * @throws Exception if a validation fails while creating the Entities
   * @throws PDOException if the query fails to execute on the server
   * @throws UserAlreadyExistsException if the active state change from false to true and than another users is already active with the same nivol
   */
  public function updateActiveAndRole(UserEntity $userEntity, int $ulId, int $roleId):bool
  {

    $oldUser = $this->getUserInfoWithUserId($userEntity->id, $ulId, $roleId);
    
    if( $oldUser->active != $userEntity->active && $userEntity->active == 1 )
    {
      $this->checkExistingUserWithNivol($oldUser->nivol);
    }


    $sql = "
UPDATE        `users`    u
  INNER JOIN  `queteur`  q
  ON         u.queteur_id = q.id
SET          u.active     = :active,
             u.role       = :role
WHERE        u.id         = :id
AND          u.role      != 9
";

    $parameters = [
      "id"     => $userEntity->id,
      "active" => $userEntity->active===true?"1":"0",
      "role"   => $userEntity->role
    ];


    if($roleId != 9)
    {//allow super admin to update users from any UL
      $sql .= "
AND     q.ul_id = :ul_id      
";
      $parameters["ul_id"] = $ulId;
    }


    $stmt = $this->db->prepare($sql);
    $stmt->execute($parameters);

    if($stmt->rowCount() == 1)
    {
      return true;
    }
    return false;
  }

  /**
   * Get the current number of Users for the Unite Local
   *
   * @param int           $ulId     Id of the UL of the user (from JWT Token, to be sure not to update other UL data)
   * @return int the number of users
   * @throws PDOException if the query fails to execute on the server
   */
  public function getNumberOfUser(int $ulId):int
  {
    $sql="
    SELECT count(1) as cnt
    FROM   users u, queteur q
    WHERE  q.ul_id = :ul_id
    AND    q.id    = u.queteur_id 
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(["ul_id" => $ulId]);
    $row = $stmt->fetch();
    return $row['cnt'];
  }
}
