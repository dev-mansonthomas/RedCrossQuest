<?php
namespace RedCrossQuest\DBService;

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
}
