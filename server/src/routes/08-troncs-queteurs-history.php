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
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur_history', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId           = $decodedToken->getUlId  ();
    $params         = $request->getQueryParams();
    $troncQueteurId = $this->clientInputValidator->validateInteger('tronc_queteur_id', getParam($params,'tronc_queteur_id'), 1000000, true);

    $troncQueteurs = $this->troncQueteurDBService->getTroncQueteurHistoryById($troncQueteurId, $ulId);

    return $response->getBody()->write(json_encode($troncQueteurs));
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while fetching the error history of a tronc_queteur($troncQueteurId)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



