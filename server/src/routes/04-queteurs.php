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
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

  $params = $request->getQueryParams();
  $ulId   = $decodedToken->getUlId  ();
  $roleId = $decodedToken->getRoleId();

  $this->logger->info( "search queteur with roleId", array("roleId"=>$roleId, "admin_ul_id exists  "=>array_key_exists('admin_ul_id',$params)));


  if($this->clientInputValidator->validateString("action", getParam($params,'action'), 30 , false ) == "searchSimilarQueteurs")
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
    $query        = "";
    $searchType   = "";
    $secteur      = "";
    $active       = "";
    $rcqUser      = "";
    $benevoleOnly = "";

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
      $benevoleOnly = $this->clientInputValidator->validateBoolean("benevoleOnly"    , getParam($params,'benevoleOnly'    ), false, false );
      $queteurIds   = $this->clientInputValidator->validateString ("queteurIds"      , getParam($params,'queteurIds'      ), 50   , false );
      $QRSearchType = $this->clientInputValidator->validateInteger('QRSearchType'    , getParam($params,'QRSearchType'    ), 5    , false );


      $queteurs = $this->queteurDBService->searchQueteurs($query, $searchType, $secteur, $ulId, $active, $benevoleOnly, $rcqUser, $queteurIds, $QRSearchType);

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
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();

    $queteurId        = (int)$args['id'];
    $queteur          = $this->queteurDBService->getQueteurById($queteurId);

    if($queteur->ul_id != $ulId && $roleId != 9)
    {
      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'permission denied']));
      return $response401;
    }

    if($roleId >= 4)
    {//localAdmin & superAdmin
      $user = $this->userDBService->getUserInfoWithQueteurId($queteurId, $ulId, $roleId);
      $queteur->user = $user;
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


/**
 * update un queteur
 *
 * Dispo pour les roles de 2 à 9
 */
$app->put('/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}', function ($request, $response, $args)
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

    $this->logger->error("Queteur",array('queteurEntity'=>$queteurEntity));

    if($this->clientInputValidator->validateString("action", getParam($params,'action'), 10 , false ) == "anonymize")
    {
      $queteurOriginalData   = $this->queteurDBService->getQueteurById($queteurEntity->id);
      $token                 = $this->queteurDBService->anonymize($queteurOriginalData->id, $ulId, $roleId, $userId);

      $this->mailer->sendAnonymizationEmail($queteurOriginalData, $token);
      $queteurAnonymizedData = $this->queteurDBService->getQueteurById($queteurEntity->id);

      return $response->getBody()->write(json_encode($queteurAnonymizedData));
    }
    else
    {//regular Update

      //restore the leading +
      $queteurEntity->mobile = "+".$queteurEntity->mobile;
      $this->queteurDBService->update($queteurEntity, $ulId, $roleId);
    }
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while updating queteur", array('decodedToken'=>$decodedToken, "Exception"=>$e, "queteurEntity"=>$queteurEntity));
    throw $e;
  }
});

/* *
 * Upload files for one queteur
 *
 * Dispo pour les roles de 2 à 9

$app->put('/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}/fileUpload', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();

    //$this->logger->info("Uploading file for UL ID='$ulId' and queteurId='$queteurId''", array('decodedToken'=>$decodedToken));


    //$queteurDBService = new QueteurDBService($this->db, $this->logger);
//    $input            = $request->getParsedBody();
//    $queteurEntity    = new QueteurEntity($input, $this->logger);
    //restore the leading +
//    $queteurEntity->mobile = "+".$queteurEntity->mobile;
//    $queteurDBService->update($queteurEntity, $ulId);
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while uploading files", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
  return $response;
});
 */

/**
 * Crée un nouveau queteur
 */
$app->post('/{role-id:[2-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();
    $params = $request->getQueryParams();

    if($this->clientInputValidator->validateString("action", getParam($params,'action'), 20 , false ) == "markAllAsPrinted")
    {
      $this->queteurDBService->markAllAsPrinted($ulId);
    }
    else
    {
      $input = $request->getParsedBody();

      $queteurEntity = new QueteurEntity($input, $this->logger);
      //restore the leading +
      $queteurEntity->mobile = "+".$queteurEntity->mobile;
      $queteurId             = $this->queteurDBService->insert($queteurEntity, $ulId, $roleId);

      return $response->getBody()->write(json_encode(array('queteurId' => $queteurId), JSON_NUMERIC_CHECK));
    }


  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while creating a new Queteur", array('decodedToken'=>$decodedToken, "Exception"=>$e, "queteurEntity"=>$queteurEntity));
    throw $e;
  }
});


