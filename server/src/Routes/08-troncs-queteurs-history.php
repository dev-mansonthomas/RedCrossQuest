<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:35
 */

require '../../vendor/autoload.php';

/********************************* TRONC_QUETEUR ****************************************/


/**
 * récupère l'historique d'un tronc_queteur par son id
 *
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur_history', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId           = (int)$args['ul-id'];

    $params         = $request->getQueryParams();
    $troncQueteurId = (int)$params['tronc_queteur_id'];

    $troncQueteurs = $this->troncQueteurDBService->getTroncQueteurHistoryById($troncQueteurId, $ulId);

    $response->getBody()->write(json_encode($troncQueteurs));

    return $response;
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while fetching the error history of a tronc_queteur($troncQueteurId)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



