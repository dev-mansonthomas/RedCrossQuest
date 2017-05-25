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
 * récupère les queteurs
 *
 * Dispo pour tout les roles
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{

  try
  {
    $ulId   = (int)$args['ul-id'];
    $roleId = (int)$args['role-id'];

    $this->logger->addInfo("Queteur list - UL ID '".$ulId."'' role ID : $roleId");

    $queteurDBService = new QueteurDBService($this->db, $this->logger);
    $params = $request->getQueryParams();

    if(array_key_exists('admin_ul_id',$params) && $roleId == 9)
    {
      $adminUlId = $params['admin_ul_id'];

      $this->logger->addInfo("Queteur list - UL ID:'$ulId' is overridden by superadmin to UL-ID: '$adminUlId' role ID:$roleId");

      $ulId = $adminUlId;
    }

    if(array_key_exists('q',$params))
    {
      $this->logger->addInfo("Queteur with query string '".$params['q']."''");
      $queteurs = $queteurDBService->getQueteurs($params['q'], $ulId);
    }
    else if(array_key_exists('searchType',$params))
    {
      $this->logger->addInfo("Queteur by search type '".$params['searchType']."");
      $queteurs = $queteurDBService->getQueteursBySearchType($params['searchType'], $ulId);
    }
    else
    {
      $this->logger->addInfo("Queteur by search type defaulted to type='0'");
      $queteurs = $queteurDBService->getQueteursBySearchType(0, $ulId);
    }


    $response->getBody()->write(json_encode($queteurs));

    return $response;
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }

});


/**
 * Récupère un queteur
 *
 * Dispo pour tout les roles
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId   = (int)$args['ul-id'];
    $roleId = (int)$args['role-id'];

    $queteurId        = (int)$args['id'];
    $queteurDBService = new QueteurDBService($this->db, $this->logger);

    $queteur          = $queteurDBService->getQueteurById($queteurId);
    $referent         = $queteurDBService->getQueteurById($queteur->referent_volunteer);
    $queteur->referent_volunteer_entity = ["id"=>$referent->id, "first_name"=>$referent->first_name,"last_name"=>$referent->last_name,"nivol"=>$referent->nivol];


    if($roleId >= 5)
    {//localAdmin & superAdmin
      $userDBService = new UserDBService($this->db, $this->logger);

      $user = $userDBService->getUserInfoWithQueteurId($queteurId, $ulId, $roleId);
      $queteur->user = $user;
    }
    
    $response->getBody()->write(json_encode($queteur));
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
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
  try
  {
    $ulId   = (int)$args['ul-id'  ];
    $roleId = (int)$args['role-id'];

    $this->logger->addDebug("Updating queteur for UL='$ulId', roleId='$roleId'");

    $files = $request->getUploadedFiles();
    $this->logger->addDebug("file upload : ".print_r($files, true));


    $queteurDBService = new QueteurDBService($this->db, $this->logger);
    $input            = $request->getParsedBody();
    $queteurEntity    = new QueteurEntity($input);

    //restore the leading +
    $queteurEntity->mobile = "+".$queteurEntity->mobile;
    
    $queteurDBService->update($queteurEntity, $ulId, $roleId);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
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
  try
  {
    $ulId      = (int)$args['ul-id'];
    $queteurId = (int)$args['id'];

    $this->logger->addInfo("Uploading file for UL ID='$ulId' and queteurId='$queteurId''");

    $this->logger->addInfo(print_r($request->getParsedBody(), true));

    $queteurDBService = new QueteurDBService($this->db, $this->logger);
//    $input            = $request->getParsedBody();
//    $queteurEntity    = new QueteurEntity($input);
    //restore the leading +
//    $queteurEntity->mobile = "+".$queteurEntity->mobile;
//    $queteurDBService->update($queteurEntity, $ulId);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});

/**
 * Crée un nouveau queteur
 */
$app->post('/{role-id:[2-9]}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];

    $this->logger->addInfo("Request UL ID '".$ulId."''");
    $queteurDBService = new QueteurDBService($this->db, $this->logger);
    $input = $request->getParsedBody();

    $queteurEntity = new QueteurEntity($input);
    //restore the leading +
    $queteurEntity->mobile = "+".$queteurEntity->mobile;

    $this->logger->error("queteurs", [$queteurEntity]);

    $queteurId = $queteurDBService->insert($queteurEntity, $ulId);

    //TODO do this stuff inside getQueteurById()
    $queteur          = $queteurDBService->getQueteurById($queteurId);
    $referent         = $queteurDBService->getQueteurById($queteur->referent_volunteer);
    $queteur->referent_volunteer_entity = ["id"=>$referent->id, "first_name"=>$referent->first_name,"last_name"=>$referent->last_name,"nivol"=>$referent->nivol];

    $response->getBody()->write(json_encode($queteur));
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});


