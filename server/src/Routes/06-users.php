<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use \RedCrossQuest\DBService\QueteurDBService;
use \RedCrossQuest\DBService\UserDBService;
use \RedCrossQuest\Entity\QueteurEntity;
use \RedCrossQuest\Entity\UserEntity;

include_once("../../src/Entity/QueteurEntity.php");
/********************************* QUETEUR ****************************************/



/**
 * Crée un nouveau user
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/users', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $userDBService = new UserDBService($this->db, $this->logger);
    $input = $request->getParsedBody();

    $userEntity = new UserEntity($input);

    $userDBService->insert($userEntity->nivol, $userEntity->queteur_id);

    $user = $userDBService->getUserInfoWithQueteurId($userEntity->queteur_id, $ulId);
    
    $userDBService->sendInit($userEntity->nivol);

    $response->getBody()->write(json_encode($user));
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});

$app->put('/{role-id:[4-9]}/ul/{ul-id}/users/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $params = $request->getQueryParams();

    if(array_key_exists('action', $params))
    {
      $action         = $params['action'];
      $userDBService  = new UserDBService($this->db, $this->logger);
      $input          = $request->getParsedBody();
      $userEntity     = new UserEntity($input);

      if ($action =="saveReturnDate")
      {
        $userDBService->updateActiveAndRole($userEntity);
      }
      else
      {
        $userDBService->sendInit($userEntity->nivol);
      }
    }
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});


