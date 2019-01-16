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
$app->get('/{role-id:[1-9]}/ul/{ul-id}/troncs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = (int)$args['ul-id'];


    $params = $request->getQueryParams();

    $active     = array_key_exists('active'     ,$params)?$params['active'    ]:1;
    $type       = array_key_exists('type'       ,$params)?$params['type'      ]:null;

    if(array_key_exists('q',$params))
    {
      $troncs = $this->troncDBService->getTroncs($params['q'], $ulId, $active, $type);
    }
    else if(array_key_exists('searchType',$params))
    {
      $troncs = $this->troncDBService->getTroncsBySearchType($params['searchType'], $ulId, $type);
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
    $this->logger->addError("Error while getting list of troncs", array('decodedToken'=>$decodedToken, 'exception'=>$e));
    throw $e;
  }
});

/*
 * récupère le détails d'un tronc (a enrichir avec les troncs_queteurs associés)
 *
 * */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/troncs/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId    = (int)$args['ul-id'];
    $troncId = (int)$args['id'   ];

    $tronc = $this->troncDBService->getTroncById($troncId, $ulId);
    $response->getBody()->write(json_encode($tronc));

    return $response;

  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while getting tronc by id '$troncId', ulId='$ulId'", array('decodedToken'=>$decodedToken, 'exception'=>$e));
    throw $e;
  }
});



/**
 * Update le tronc, seulement pour l'admin
 *
 * */
$app->put('/{role-id:[4-9]}/ul/{ul-id}/troncs/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId    = (int)$args['ul-id'];


    $input            = $request->getParsedBody();
    $troncEntity      = new TroncEntity($input, $this->logger);

    $this->troncDBService->update($troncEntity, $ulId);
    return ;

  }
  catch(\Exception $e)
  {
    $this->logger->addError("error while updating tronc ulId='$ulId'", array('decodedToken'=>$decodedToken, 'troncEntity'=>$troncEntity, 'exception'=>$e));
    throw $e;
  }
});



/**
 * Update le tronc, seulement pour l'admin
 *
 * */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/troncs', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId    = (int)$args['ul-id'];


    $input            = $request->getParsedBody();
    $troncEntity      = new TroncEntity($input, $this->logger);

    $this->troncDBService->insert($troncEntity, $ulId);
    return ;

  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while inserting Tronc for ulId='$ulId'", array('decodedToken'=>$decodedToken, 'troncEntity'=>$troncEntity, 'exception'=>$e));
    throw $e;
  }
});


