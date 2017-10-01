<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:36
 */

use \RedCrossQuest\DBService\PointQueteDBService;

/********************************* POINT_QUETE ****************************************/

$app->get('/{role-id:[1-9]}/ul/{ul-id}/pointQuetes', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = (int)$args['ul-id'];

    $pointQueteDBService = new PointQueteDBService($this->db, $this->logger);


    //$this->logger->addInfo("PointQuetes query for ul: $ulId");
    $pointQuetes = $pointQueteDBService->getPointQuetes($ulId);


    $response->getBody()->write(json_encode($pointQuetes));

    return $response;

  }
  catch(Exception $e)
  {
    $this->logger->addError($e, array('decodedToken'=>$decodedToken));
    throw $e;
  }

});

