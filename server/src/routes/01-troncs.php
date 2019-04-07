<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:33
 */

require '../../vendor/autoload.php';

use \RedCrossQuest\Entity\TroncEntity;

/********************************* TRONC ****************************************/


/**
 récupère la liste des troncs (affichage des Troncs & QRCode Troncs)
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/troncs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId();
    $params = $request->getQueryParams();
    $active = $this->clientInputValidator->validateBoolean("active", getParam($params,'active'), false, true);

    if(!array_key_exists('type'     ,$params))
      $type   = null;
    else
      $type   = $this->clientInputValidator->validateInteger("type", getParam($params, 'type'), 5, true);

    if(array_key_exists('q',$params))
    {
      $q =  $this->clientInputValidator->validateInteger("q", $params['q'], 1000000  , true );

      $troncs = $this->troncDBService->getTroncs($q, $ulId, $active, $type);
    }
    else if(array_key_exists('searchType',$params))
    {
      $searchType = $this->clientInputValidator->validateInteger("searchType", getParam($params, 'searchType'), 2, true);
      $troncs     = $this->troncDBService->getTroncsBySearchType($searchType, $ulId, $type);
    }
    else
    {
      $troncs = $this->troncDBService->getTroncs(null,$ulId, $active, $type );
    }

    $response->getBody()->write(json_encode($troncs));

    return $response;

  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while getting list of troncs", array('decodedToken'=>$decodedToken, 'exception'=>$e));
    throw $e;
  }
});
/*
 * récupère le détails d'un tronc (a enrichir avec les troncs_queteurs associés)
 *
 * */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/troncs/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = $decodedToken->getUlId();


    $troncId = $this->clientInputValidator->validateInteger('id', $args['id'], 1000000, true);

    $tronc = $this->troncDBService->getTroncById($troncId, $ulId);
    $response->getBody()->write(json_encode($tronc));

    return $response;

  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while getting tronc by id '$troncId', ulId='$ulId'", array('decodedToken'=>$decodedToken, 'exception'=>$e));
    throw $e;
  }
});



/**
 * Update le tronc, seulement pour l'admin
 *
 * */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/troncs/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = $decodedToken->getUlId();


    $input            = $request->getParsedBody();
    $troncEntity      = new TroncEntity($input, $this->logger);

    $this->troncDBService->update($troncEntity, $ulId);
    return ;

  }
  catch(\Exception $e)
  {
    $this->logger->error("error while updating tronc ulId='$ulId'", array('decodedToken'=>$decodedToken, 'troncEntity'=>$troncEntity, 'exception'=>$e));
    throw $e;
  }
});



/**
 * Update le tronc, seulement pour l'admin
 *
 * */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/troncs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = $decodedToken->getUlId();

    $input            = $request->getParsedBody();
    $troncEntity      = new TroncEntity($input, $this->logger);

    $this->troncDBService->insert($troncEntity, $ulId);
    return ;

  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while inserting Tronc for ulId='$ulId'", array('decodedToken'=>$decodedToken, 'troncEntity'=>$troncEntity, 'exception'=>$e));
    throw $e;
  }
});


