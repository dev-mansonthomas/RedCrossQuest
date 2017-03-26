<?php
namespace RedCrossQuest;

class UserMapper extends Mapper
{
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
