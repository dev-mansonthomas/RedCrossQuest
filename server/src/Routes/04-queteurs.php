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


include_once("../../src/Entity/QueteurEntity.php");
/********************************* QUETEUR ****************************************/


/**
 * récupère les queteurs
 *
 * Dispo pour tout les roles
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

  $params       = $request->getQueryParams();
  $ulId         = (int)$args['ul-id'];
  $roleId       = (int)$args['role-id'];

  $queteurDBService = new QueteurDBService($this->db, $this->logger);


  if(array_key_exists('action', $params) && $params['action'] == "searchSimilarQueteurs")
  {

    $firstName  = array_key_exists('first_name', $params)?$params['first_name' ]:null;
    $lastName   = array_key_exists('last_name' , $params)?$params['last_name'  ]:null;
    $nivol      = array_key_exists('nivol'     , $params)?$params['nivol'      ]:null;


    $queteurs = $queteurDBService->searchSimilarQueteur($ulId, $firstName, $lastName, $nivol);
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

      if($roleId >= 4)
      {
        if(array_key_exists('anonymization_token',$params))
        {// If the token is given, then other search criteria are ignored
          $queteurs = $queteurDBService->getQueteurByAnonymizationToken($params['anonymization_token'],  $ulId, $roleId);
          $response->getBody()->write(json_encode($queteurs));
          return $response;
        }


        if(array_key_exists('admin_ul_id',$params) && $roleId > 4)
        {
          $adminUlId = $params['admin_ul_id'];
          //$this->logger->addInfo("Queteur list - UL ID:'$ulId' is overridden by superadmin to UL-ID: '$adminUlId' role ID:$roleId", array('decodedToken'=>$decodedToken));
          $ulId = $adminUlId;
        }

      }



      $query        = array_key_exists('q'             ,$params)?$params['q'            ]:null;
      $searchType   = array_key_exists('searchType'    ,$params)?$params['searchType'   ]:null;
      $secteur      = array_key_exists('secteur'       ,$params)?$params['secteur'      ]:null;
      $active       = array_key_exists('active'        ,$params)?$params['active'       ]:1;
      $rcqUser      = array_key_exists('rcqUser'       ,$params)?$params['rcqUser'      ]:0;
      $benevoleOnly = array_key_exists('$benevoleOnly' ,$params)?$params['$benevoleOnly']:0;
      $queteurIds   = array_key_exists('queteurIds'    ,$params)?$params['queteurIds'   ]:null;
      $QRSearchType = array_key_exists('QRSearchType'  ,$params)?$params['QRSearchType' ]:0;

      //$this->logger->addInfo( "Queteurs search: query:'$query', searchType:'$searchType', secteur:'$secteur', UL ID:'$ulId', role ID : $roleId", array('decodedToken'=>$decodedToken));

      if($ulId == null || $ulId == '')
      {
        $ulId = (int)$decodedToken->getUlId();
      }

      $queteurs = $queteurDBService->searchQueteurs($query, $searchType, $secteur, $ulId, $active, $benevoleOnly, $rcqUser, $queteurIds, $QRSearchType);

      $response->getBody()->write(json_encode($queteurs));

      return $response;
    }
    catch(\Exception $e)
    {
      $this->logger->addError("error while fetching queteur with the following parameters query=$query, searchType=$searchType, secteur=$secteur, ulId=$ulId, active=$active, benevoleOnly=$benevoleOnly", array('decodedToken'=>$decodedToken, "Exception"=>$e));
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
    $ulId   = (int)$args['ul-id'];
    $roleId = (int)$args['role-id'];

    $queteurId        = (int)$args['id'];
    $queteurDBService = new QueteurDBService($this->db, $this->logger);

    $queteur          = $queteurDBService->getQueteurById($queteurId);

    if($queteur->ul_id != $ulId && $roleId != 9)
    {
      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode("{error:'permission denied'}"));
      return $response401;
    }

    if($roleId >= 4)
    {//localAdmin & superAdmin
      $userDBService = new UserDBService($this->db, $this->logger);

      $user = $userDBService->getUserInfoWithQueteurId($queteurId, $ulId, $roleId);
      $queteur->user = $user;
    }

    $response->getBody()->write(json_encode($queteur));
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while fetching queteur $queteurId ", array('decodedToken'=>json_encode($decodedToken), "Exception"=>json_encode($e)));
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
    $ulId   = (int)$args['ul-id'  ];
    $roleId = (int)$args['role-id'];
    $userId = $decodedToken->getUid();
    $params = $request->getQueryParams();

    $queteurDBService = new QueteurDBService($this->db, $this->logger);
    $input            = $request->getParsedBody();
    $queteurEntity    = new QueteurEntity($input, $this->logger);

    $this->logger->addError("Queteur",array('queteurEntity'=>$queteurEntity));
    if(array_key_exists('action', $params) && $params['action'] == "anonymize")
    {
      $queteurOriginalData = $queteurDBService->getQueteurById($queteurEntity->id);
      $token               = $queteurDBService->anonymize($queteurOriginalData->id, $ulId, $roleId, $userId);
      $this->mailer->sendAnonymizationEmail($queteurOriginalData, $token);
      $queteurAnonymizedData = $queteurDBService->getQueteurById($queteurEntity->id);

      $response->getBody()->write(json_encode($queteurAnonymizedData));
    }
    else
    {//regular Update

      //restore the leading +
      $queteurEntity->mobile = "+".$queteurEntity->mobile;

      $queteurDBService->update($queteurEntity, $ulId, $roleId);

    }

  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while updating queteur", array('decodedToken'=>$decodedToken, "Exception"=>$e, "queteurEntity"=>$queteurEntity));
    throw $e;
  }

  return $response;
});

/**
 * Upload files for one queteur
 *
 * Dispo pour les roles de 2 à 9
 */
$app->put('/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}/fileUpload', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

  try
  {
    $ulId      = (int)$args['ul-id'];
    $queteurId = (int)$args['id'];

    //$this->logger->addInfo("Uploading file for UL ID='$ulId' and queteurId='$queteurId''", array('decodedToken'=>$decodedToken));


    //$queteurDBService = new QueteurDBService($this->db, $this->logger);
//    $input            = $request->getParsedBody();
//    $queteurEntity    = new QueteurEntity($input, $this->logger);
    //restore the leading +
//    $queteurEntity->mobile = "+".$queteurEntity->mobile;
//    $queteurDBService->update($queteurEntity, $ulId);
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while uploading files", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
  return $response;
});

/**
 * Crée un nouveau queteur
 */
$app->post('/{role-id:[2-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = (int)$args['ul-id'  ];
    $roleId = (int)$args['role-id'];
    $params = $request->getQueryParams();

    $queteurDBService = new QueteurDBService($this->db, $this->logger);

    if(array_key_exists('action', $params) && $params['action'] == "markAllAsPrinted")
    {
      $queteurDBService->markAllAsPrinted($ulId);
    }
    else
    {
      $input = $request->getParsedBody();

      $queteurEntity = new QueteurEntity($input, $this->logger);
      //restore the leading +
      $queteurEntity->mobile = "+".$queteurEntity->mobile;
      $queteurId          = $queteurDBService->insert($queteurEntity, $ulId, $roleId);

      $response->getBody()->write(json_encode(array('queteurId' =>$queteurId), JSON_NUMERIC_CHECK));
    }


  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while creating a new Queteur", array('decodedToken'=>$decodedToken, "Exception"=>$e, "queteurEntity"=>$queteurEntity));
    throw $e;
  }
  return $response;
});


