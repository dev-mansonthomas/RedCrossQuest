<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:36
 */

require '../../vendor/autoload.php';

use \RedCrossQuest\Entity\PointQueteEntity;

/********************************* POINT_QUETE ****************************************/

/**
 * fetch point de quete for an UL
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/pointQuetes/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

  $ulId   = $decodedToken->getUlId  ();
  $roleId = $decodedToken->getRoleId();


  $id     = $this->clientInputValidator->validateInteger('id', $args['id'], 1000000, true);

  try
  {
    $pointQuete = $this->pointQueteDBService->getPointQueteById($id, $ulId, $roleId);

    return $response->getBody()->write(json_encode($pointQuete));

  }
  catch(\Exception $e)
  {
    $this->logger->error("error while getting point_quete '$id' of ul with id $ulId ", array('decodedToken'=>json_encode($decodedToken), "Exception"=>json_encode($e)));
    throw $e;
  }


});


/**
 * fetch point de quete for an UL
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/pointQuetes', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

 try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();
    $params = $request->getQueryParams();
    $action = $this->clientInputValidator->validateString("action", getParam($params,'action'), 40 , false );

    if( $action === "search")
    {//admin function
      if(array_key_exists('admin_ul_id',$params) && $roleId == 9)
      {
        $ulId = $this->clientInputValidator->validateInteger('admin_ul_id', $params['admin_ul_id'], 1000, true);
        //$this->logger->info("PointQuete list - UL ID:'".$decodedToken->getUlId  ()."' is overridden by superadmin to UL-ID: '$adminUlId' role ID:$roleId", array('decodedToken'=>$decodedToken));
      }

      $query            = $this->clientInputValidator->validateString ("q"               , getParam($params,'q'               ), 40   , false);
      $point_quete_type = $this->clientInputValidator->validateInteger('point_quete_type', getParam($params,'point_quete_type'), 10   , false);
      $active           = $this->clientInputValidator->validateBoolean("active"          , getParam($params,'active'          ), false, true );

      //$this->logger->info("PointQuetes query for ul: $ulId");
      $pointQuetes = $this->pointQueteDBService->searchPointQuetes($query, $point_quete_type, $active, $ulId);
    }
    else
    {//used for the dropdown to select point de quete while preparing a tronc
      $pointQuetes = $this->pointQueteDBService->getPointQuetes($ulId);
    }

    $response->getBody()->write(json_encode($pointQuetes));
    return $response;

  }
  catch(\Exception $e)
  {
    $this->logger->error("error while getting point_quete of ul with id $ulId ", array('decodedToken'=>json_encode($decodedToken), "Exception"=>json_encode($e)));
    throw $e;
  }

});


/**
 * update un point quete
 *
 * Dispo pour les roles de 2 à 9
 */
$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/pointQuetes/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();

    $input               = $request->getParsedBody();
    $pointQueteEntity    = new PointQueteEntity($input, $this->logger);

    $this->pointQueteDBService->update($pointQueteEntity, $ulId, $roleId);
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while updating point quete", array('decodedToken'=>$decodedToken, "Exception"=>$e, "pointQueteEntity"=>$pointQueteEntity));
    throw $e;
  }

  return $response;
});


/**
 * Crée un nouveau queteur
 */
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/pointQuetes', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId                = $decodedToken->getUlId  ();
    $input               = $request->getParsedBody();
    $pointQueteEntity    = new PointQueteEntity($input, $this->logger);

    $pointQueteId = $this->pointQueteDBService->insert            ($pointQueteEntity, $ulId);

    $response->getBody()->write(json_encode(array('pointQueteId' =>$pointQueteId), JSON_NUMERIC_CHECK));
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while creating a new PointQuete", array('decodedToken'=>$decodedToken, "Exception"=>$e, "pointQueteEntity"=>$pointQueteEntity));
    throw $e;
  }
  return $response;
});
