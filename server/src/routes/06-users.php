<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use \RedCrossQuest\Entity\UserEntity;

/********************************* QUETEUR ****************************************/


/**
 * Update Role or active/inactive
 * or
 * send email for Password reset
 */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/users/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();
    $params = $request->getQueryParams();

    if(array_key_exists('action', $params))
    {
      $action         = $this->clientInputValidator->validateString("action", $params['action'], 20 , true);
      $input          = $request->getParsedBody();
      $userEntity     = new UserEntity($input, $this->logger);

      if($action == "update")
      {
        $this->logger->info("Updating user activeAndRole", array("decodedToken"=>$decodedToken, "updatedUser" => $userEntity));

        if($userEntity->role > $decodedToken->getRoleId() || $userEntity->role <= 0)
        {
          $this->logger->info("Connected user is trying to grand higher privilege than his to someone else", array("decodedToken"=>$decodedToken, "updatedUser" => $userEntity));
          throw new \Exception("PDOException(code: 42000): SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ')' at line 14 at /app/src/DBService/UsersDBService.php:47");
        }



        try
        {
          $numberOfUpdatedRows = $this->userDBService->updateActiveAndRole($userEntity, $decodedToken->getUlId(), $decodedToken->getRoleId());
        }
        catch(\RedCrossQuest\Exception\UserAlreadyExistsException $exception)
        {
          $this->logger->error($exception->getMessage(), [$exception->users, $exception]);
          return $response->getBody()->write(json_encode(["error" =>$exception->getMessage()]));
        }

        if($numberOfUpdatedRows == 0)
        {
          $this->logger->info("Updating user activeAndRole FAILED, no row updated", array("decodedToken"=>$decodedToken, "updatedUser" => $userEntity));
          return $response->getBody()->write(json_encode($userEntity));//return the original objects
        }
      }
      else
      {//send init mail to user                                                             //super user can do that for all users.
        $queteur          = $this->queteurDBService->getQueteurById($userEntity->queteur_id, $roleId ==9 ? null: $ulId);
        $uuid             = $this->userDBService   ->sendInit      ($userEntity->nivol     );
        $this->mailer->sendInitEmail($queteur, $uuid);

      }
      $userEntity = $this->userDBService->getUserInfoWithUserId($userEntity->id, $ulId, $roleId);
      return $response->getBody()->write(json_encode($userEntity));
    }
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while updating ActiveAndRole or sending init password email", array('decodedToken'=>$decodedToken, "Exception"=>$e, "userEntity"=>$userEntity));
    throw $e;
  }
});


/**
 * create a new user
 */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/users', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();

    $input      = $request->getParsedBody();
    $userEntity = new UserEntity($input, $this->logger);
    $queteur    = $this->queteurDBService->getQueteurById($userEntity->queteur_id, $roleId ==9 ? null: $ulId);

    //check NIVOL has not been changed
    if($userEntity->nivol != $queteur->nivol)
    {
      throw new \Exception("UserEntity NIVOL (from web form) & Queteur NIVOL (from DB) do not match ('".$userEntity->nivol."'!='".$queteur->nivol."')");
    }

    if($queteur->ul_id != $ulId )
    {
      if($roleId != 9)
      {
        throw new \Exception("current user is trying to create an RCQ user for another UL and is not super Admin");
      }
      else
      {
        $this->logger->info("SuperAdmin is creating an user for another UL ".$queteur->ul_id." NIVOL:'".$userEntity->nivol."' - QueteurId:'".$userEntity->queteur_id."'");
      }
    }

    try
    {
      $this->userDBService->insert($userEntity->nivol, $userEntity->queteur_id);
    }
    catch(\RedCrossQuest\Exception\UserAlreadyExistsException $exception)
    {
      $this->logger->error($exception->getMessage(), [$exception->users, $exception]);

      return $response->getBody()->write(json_encode(["error" =>$exception->getMessage()]));
    }


    $user = $this->userDBService->getUserInfoWithQueteurId($userEntity->queteur_id, $ulId, $roleId);
    $uuid = $this->userDBService->sendInit                ($userEntity->nivol, true);

    $this->mailer->sendInitEmail($queteur, $uuid, true);

    return $response->getBody()->write(json_encode($user));
  }
  catch(\Exception $e)
  {
    $this->logger->error("error while creating a new user", array('decodedToken'=>$decodedToken, "Exception"=>$e, "userEntity"=>$userEntity));
    throw $e;
  }
});



