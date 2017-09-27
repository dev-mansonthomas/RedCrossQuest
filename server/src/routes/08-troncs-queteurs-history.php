<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:35
 */


use \RedCrossQuest\DBService\TroncQueteurDBService;

include_once("../../src/DBService/TroncQueteurDBService.php");

/********************************* TRONC_QUETEUR ****************************************/


/**
 * récupère l'historique d'un tronc_queteur par son id
 *
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur_history', function ($request, $response, $args)
{
  try
  {
    $ulId           = (int)$args['ul-id'];

    $params         = $request->getQueryParams();
    $troncQueteurId = (int)$params['tronc_queteur_id'];;

    $this->logger->debug("Getting history of tronc_queteur with id '".$troncQueteurId."'");

    $troncQueteurDBService = new TroncQueteurDBService($this->db, $this->logger);

    $troncQueteurs = $troncQueteurDBService->getTroncQueteurHistoryById($troncQueteurId, $ulId);

    $response->getBody()->write(json_encode($troncQueteurs, JSON_NUMERIC_CHECK));

    return $response;
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }
});



