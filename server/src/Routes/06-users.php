<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use \RedCrossQuest\DBService\QueteurDBService;
use \RedCrossQuest\DBService\UserDBService;

use \RedCrossQuest\Entity\UserEntity;

include_once("../../src/Entity/QueteurEntity.php");
/********************************* QUETEUR ****************************************/



$app->put('/{role-id:[4-9]}/ul/{ul-id}/users/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = (int)$args['ul-id'  ];
    $roleId = (int)$args['role-id'];
    
    $params = $request->getQueryParams();

    if(array_key_exists('action', $params))
    {
      $action         = $params['action'];
      $input          = $request->getParsedBody();
      $userEntity     = new UserEntity($input);

      $userDBService  = new UserDBService($this->db, $this->logger);
      if ($action =="update")
      {
        //$this->logger->addDebug("updating user RoleAndActive ".print_r($userEntity, true));
        $userDBService->updateActiveAndRole($userEntity);
      }
      else
      {//send init mail to user
        $queteurDBService = new QueteurDBService($this->db, $this->logger);
        $queteur = $queteurDBService->getQueteurById($userEntity->queteur_id);

        //$this->logger->addDebug("sendInit mail to user '".$userEntity->id."'' ".print_r($queteur, true));

        $uuid = $userDBService->sendInit($userEntity->nivol);
        $this->mailer->sendInitEmail($queteur, $uuid);

      }
      $userEntity = $userDBService->getUserInfoWithUserId($userEntity->id, $ulId, $roleId);
      $response->getBody()->write(json_encode($userEntity));
    }
  }
  catch(Exception $e)
  {
    $this->logger->addError($e, array('decodedToken'=>$decodedToken));
  }
  return $response;
});


/**
 * Crée un nouveau user
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/users', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = (int)$args['ul-id'  ];
    $roleId = (int)$args['role-id'];

    $userDBService    = new UserDBService   ($this->db, $this->logger);
    $queteurDBService = new QueteurDBService($this->db, $this->logger);

    $input = $request->getParsedBody();
    $userEntity = new UserEntity($input);

    $queteur = $queteurDBService->getQueteurById($userEntity->queteur_id);

    //check NIVOL has not been changed
    if($userEntity->nivol != $queteur->nivol)
    {
      throw new Exception("UserEntity NIVOL (from web form) & Queteur NIVOL (from DB) do not match ('".$userEntity->nivol."'!='".$queteur->nivol."')");
    }

    if($queteur->ul_id != $ulId )
    {
      if($roleId != 9)
      {
        throw new Exception("current user is trying to create an RCQ user for another UL and is not super Admin");
      }
      else
      {
        $this->logger->addInfo("SuperAdmin is creating an user for another UL ".$queteur->ul_id." NIVOL:'".$userEntity->nivol."' - QueteurId:'".$userEntity->queteur_id."'");
      }

    }


    $userDBService->insert($userEntity->nivol, $userEntity->queteur_id);

    $user = $userDBService->getUserInfoWithQueteurId($userEntity->queteur_id, $ulId, $roleId);
    
    $uuid = $userDBService->sendInit($userEntity->nivol);
    $this->mailer->sendInitEmail($queteur, $uuid);

    $response->getBody()->write(json_encode($user));
  }
  catch(Exception $e)
  {
    $this->logger->addError($e, array('decodedToken'=>$decodedToken));
  }
  return $response;
});



