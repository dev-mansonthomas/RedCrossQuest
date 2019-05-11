<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use \RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Service\ClientInputValidator;

/********************************* QUETEUR ****************************************/

/**
 * récupère les queteurs
 *
 * Dispo pour tout les roles
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

  $params = $request->getQueryParams();
  $ulId   = $decodedToken->getUlId  ();
  $roleId = $decodedToken->getRoleId();

  $this->logger->info( "search queteur with roleId", array("roleId"=>$roleId, "admin_ul_id exists  "=>array_key_exists('admin_ul_id',$params)));

  $action = $this->clientInputValidator->validateString("action", getParam($params,'action'), 31 , false );

  if( $action == "countPendingQueteurRegistration")
  {
    $count = $this->queteurDBService->countPendingQueteurRegistration($ulId);
    $response->getBody()->write(json_encode($count));
  }
  else if($action == "listPendingQueteurRegistration")
  {
    $registrationStatus  = $this->clientInputValidator->validateInteger("registration_status", getParam($params,'registration_status'), 2 , false, 0);

    $queteurs = $this->queteurDBService->listPendingQueteurRegistration($ulId, $registrationStatus);
    $response->getBody()->write(json_encode($queteurs));
  }
  else if($action == "searchSimilarQueteurs")
  {

    $firstName  = $this->clientInputValidator->validateString("first_name", getParam($params,'first_name'), 100 , false );
    $lastName   = $this->clientInputValidator->validateString("last_name" , getParam($params,'last_name') , 100 , false );
    $nivol      = $this->clientInputValidator->validateString("nivol"     , getParam($params,'nivol')     , 15  , false );

    if(empty($firstName) && empty($lastName) && empty($nivol))
    {//if nothing is give, return empty array
      $response->getBody()->write(json_encode([]));
    }

    $queteurs = $this->queteurDBService->searchSimilarQueteur($ulId, $firstName, $lastName, $nivol);
    $response->getBody()->write(json_encode($queteurs));
  }
  else
  {
    try
    {

      if(array_key_exists('anonymization_token',$params) && strlen($params['anonymization_token']) > 0 && $roleId >= 4)
      {// If the token is given, then other search criteria are ignored
        $queteurs = $this->queteurDBService->getQueteurByAnonymizationToken(
          $this->clientInputValidator->validateString("anonymization_token", getParam($params,'anonymization_token'), 36 , true, ClientInputValidator::$UUID_VALIDATION),
          $ulId, $roleId);

        return $response->getBody()->write(json_encode($queteurs));
      }

      if(array_key_exists('admin_ul_id',$params) && $roleId == 9)
      {
        $ulId = $this->clientInputValidator->validateInteger('admin_ul_id', $params['admin_ul_id'], 1000, true);
        //$this->logger->info("Queteur list - UL ID:'".$decodedToken->getUlId  ()."' is overridden by superadmin to UL-ID: '$adminUlId' role ID:$roleId", array('decodedToken'=>$decodedToken));
      }

      $query        = $this->clientInputValidator->validateString ("q"               , getParam($params,'q'               ), 100  , false );
      $searchType   = $this->clientInputValidator->validateInteger('searchType'      , getParam($params,'searchType'      ), 5    , false );
      $secteur      = $this->clientInputValidator->validateInteger('secteur'         , getParam($params,'secteur'         ), 10   , false );
      $active       = $this->clientInputValidator->validateBoolean("active"          , getParam($params,'active'          ), false, true  );
      $rcqUser      = $this->clientInputValidator->validateBoolean("rcqUser"         , getParam($params,'rcqUser'         ), false, false );
      $rcqUserActif = $this->clientInputValidator->validateBoolean("rcqUserActif"    , getParam($params,'rcqUserActif'    ), false, false );
      $benevoleOnly = $this->clientInputValidator->validateBoolean("benevoleOnly"    , getParam($params,'benevoleOnly'    ), false, false );
      $queteurIds   = $this->clientInputValidator->validateString ("queteurIds"      , getParam($params,'queteurIds'      ), 50   , false );
      $QRSearchType = $this->clientInputValidator->validateInteger('QRSearchType'    , getParam($params,'QRSearchType'    ), 5    , false );


      $queteurs = $this->queteurDBService->searchQueteurs($query, $searchType, $secteur, $ulId, $active, $benevoleOnly, $rcqUser, $rcqUserActif, $queteurIds, $QRSearchType);

      return $response->getBody()->write(json_encode($queteurs));
    }
    catch(\Exception $e)
    {
      $this->logger->error("error while fetching queteur with the following parameters query=$query, searchType=$searchType, secteur=$secteur, ulId=$ulId, active=$active, benevoleOnly=$benevoleOnly", array('decodedToken'=>$decodedToken, "Exception"=>$e));
      throw $e;
    }
  }
});


/**
 * Récupère un queteur
 *
 * Dispo pour tout les roles
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId       = $decodedToken->getUlId  ();
    $roleId     = $decodedToken->getRoleId();
    $params     = $request->getQueryParams();
    $queteurId  = (int)$args['id'];

    if($this->clientInputValidator->validateString("action", getParam($params,'action'), 22 , false ) == "getQueteurRegistration")
    {
      $queteur          = $this->queteurDBService->getQueteurRegistration($ulId, $queteurId);
      //so that it's preset to active. No point of accepting a registration of an inactive queteur
      $queteur->active = true;
      //unset the decision to not pre select any answer
      unset($queteur->registration_approved);
      $response->getBody()->write(json_encode($queteur));
      return $response;
    }


    $queteur          = $this->queteurDBService->getQueteurById($queteurId);

    if($queteur->ul_id != $ulId && $roleId != 9)
    {
      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'permission denied']));
      return $response401;
    }

    if($roleId >= 4)
    {//localAdmin & superAdmin
      $queteur->user = $this->userDBService->getUserInfoWithQueteurId($queteurId, $ulId, $roleId);
    }

    $response->getBody()->write(json_encode($queteur));
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while fetching queteur $queteurId ", array('decodedToken'=>json_encode($decodedToken), "Exception"=>json_encode($e)));
    throw $e;
  }


  return $response;
});

$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();
    $userId = $decodedToken->getUid();
    $params = $request->getQueryParams();


    if($this->clientInputValidator->validateString("action", getParam($params,'action'), 20 , false ) == "markAllAsPrinted")
    {
      $this->queteurDBService->markAllAsPrinted($ulId);
    }

  }
  catch(Exception $e)
  {
    $this->logger->error("Error while updating queteurs", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});
/**
 * update un queteur
 *
 * Dispo pour les roles de 2 à 9
 */
$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();
    $userId = $decodedToken->getUid();
    $params = $request->getQueryParams();
    $input  = $request->getParsedBody();

    $queteurEntity = new QueteurEntity($input, $this->logger);

    if($this->clientInputValidator->validateString("action", getParam($params,'action'), 40 , false ) == "anonymize")
    {
      $queteurOriginalData   = $this->queteurDBService->getQueteurById($queteurEntity->id);
      $token                 = $this->queteurDBService->anonymize($queteurOriginalData->id, $ulId, $roleId, $userId);

      $this->mailer->sendAnonymizationEmail($queteurOriginalData, $token);
      $queteurAnonymizedData = $this->queteurDBService->getQueteurById($queteurEntity->id);

      return $response->getBody()->write(json_encode($queteurAnonymizedData));
    }
    else if($this->clientInputValidator->validateString("action", getParam($params,'action'), 40 , false ) == "associateRegistrationWithExistingQueteur")
    {
      //restore the leading +
      $queteurEntity->mobile = "+".$queteurEntity->mobile;

      $this->logger->error($queteurEntity->ul_registration_token." ".strlen($queteurEntity->ul_registration_token) == 36);


      //validate the token, if validation fails, it throws an exception
      $this->clientInputValidator->validateString("ul_registration_token", $queteurEntity->ul_registration_token, 36 , true, ClientInputValidator::$UUID_VALIDATION );

      $queteurEntity->referent_volunteer = 0;

      //as we're associating the registration to an existing queteur, it's necessarily an approval
      $queteurEntity->registration_approved = true;
      $this->queteurDBService->associateRegistrationWithExistingQueteur($queteurEntity, $userId, $ulId);

      //publishing message to pubsub so that firebase is updated
      $responseMessageIds = null;
      $messageProperties  = [
        'ulId'          => "".$ulId,
        'uId'           => "".$userId,
        'queteurId'     => "".$queteurEntity->id,
        'registrationId'=> "".$queteurEntity->registration_id
      ];

      try
      {
        $this->PubSub->publish(
          $this->settings['PubSub']['queteur_approval_topic'],
          $queteurEntity,
          $messageProperties,
          true,
          true);
      }
      catch(Exception $exception)
      {
        $this->logger->error("error while publishing registration approval - associateRegistrationWithExistingQueteur", array("messageProperties"=> $messageProperties,
          "queteurEntity"    => $queteurEntity,
          "exception"        => $exception));
        //do not rethrow
      }

      return $response->getBody()->write(json_encode(array('queteurId' => $queteurEntity->id), JSON_NUMERIC_CHECK));
    }
    else if($this->clientInputValidator->validateString("action", getParam($params,'action'), 40 , false ) == "update")
    {//regular Update

      //restore the leading +
      $queteurEntity->mobile = "+".$queteurEntity->mobile;
      $this->queteurDBService->update($queteurEntity, $ulId, $roleId);
    }

  }
  catch(Exception $e)
  {
    $this->logger->error("Error while updating queteur", array('decodedToken'=>$decodedToken, "Exception"=>$e, "queteurEntity"=>$queteurEntity));
    throw $e;
  }
});


/**
 * Crée un nouveau queteur (via une création standard, ou approval d'une inscription RedQuest)
 */
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId       ();
    $userId = $decodedToken->getUid        ();
    $roleId = $decodedToken->getRoleId     ();
    $params = $request     ->getQueryParams();

    $input  = $request->getParsedBody();
    $queteurEntity = new QueteurEntity($input, $this->logger);


    if( isset($queteurEntity->registration_id) && is_scalar($queteurEntity->registration_id) )
    {
      //restore the leading +
      $queteurEntity->mobile = "+".$queteurEntity->mobile;

      //validate the token, if validation fails, it throws an exception
      $this->clientInputValidator->validateString("ul_registration_token", $queteurEntity->ul_registration_token, 36 , true, ClientInputValidator::$UUID_VALIDATION );

      if($queteurEntity->registration_approved)
      {
        $queteurEntity->referent_volunteer = 0;
        $queteurId = $this->queteurDBService->insert($queteurEntity, $ulId, $roleId);
        $this->queteurDBService->updateQueteurRegistration($queteurEntity, $queteurId, $userId);

        //update the entity with the new ID
        $queteurEntity->id = $queteurId;
      }
      else
      {//reject
        $this->queteurDBService->updateQueteurRegistration($queteurEntity, 0, $userId);
        $queteurEntity->id = -1;
      }

      //publishing message to pubsub so that firebase is updated
      $responseMessageIds = null;
      $messageProperties  = [
        'ulId'          => "".$ulId,
        'uId'           => "".$userId,
        'queteurId'     => "".$queteurEntity->id,
        'registrationId'=> "".$queteurEntity->registration_id
      ];

      try
      {
        $this->PubSub->publish(
          $this->settings['PubSub']['queteur_approval_topic'],
          $queteurEntity,
          $messageProperties,
          true,
          true);
      }
      catch(Exception $exception)
      {
        $this->logger->error("error while publishing registration approval", array("messageProperties"=> $messageProperties,
                                                                                   "queteurEntity"    => $queteurEntity,
                                                                                   "exception"        => $exception));
        //do not rethrow
      }
      return $response->getBody()->write(json_encode(array('queteurId' => $queteurEntity->id), JSON_NUMERIC_CHECK));
    }
    else
    {// queteur creation

      //restore the leading +
      $queteurEntity->mobile = "+".$queteurEntity->mobile;

      $this->logger->info("queteur creation", array("queteur"=>$queteurEntity));
      $queteurId  = $this->queteurDBService->insert($queteurEntity, $ulId, $roleId);
      return $response->getBody()->write(json_encode(array('queteurId' => $queteurId), JSON_NUMERIC_CHECK));
    }
  }
  catch(Exception $e)
  {
    $this->logger->error("Error while creating a new Queteur", array('decodedToken'=>$decodedToken, "Exception"=>$e, "queteurEntity"=>$queteurEntity));
    throw $e;
  }
});


