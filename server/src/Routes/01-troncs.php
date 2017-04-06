<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:33
 */

use \RedCrossQuest\DBService\TroncDBService;

/********************************* TRONC ****************************************/


/**
 récupère la liste des troncs (affichage des Troncs & QRCode Troncs)
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/troncs', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $this->logger->addInfo("Troncs list, UL ID '".$ulId."''");

    $troncDBService = new TroncDBService($this->db, $this->logger);
    $params = $request->getQueryParams();


    if(array_key_exists('q',$params))
    {
      $this->logger->addInfo("Tronc by search type '".$params['q']."''");
      $troncs = $troncDBService->getTroncs($params['q'], $ulId);
    }
    else if(array_key_exists('searchType',$params))
    {
      $this->logger->addInfo("Tronc by search type '".$params['searchType']."''");
      $troncs = $troncDBService->getTroncsBySearchType($params['searchType'], $ulId);
    }
    else
    {
      $troncs = $troncDBService->getTroncs();
    }

    $response->getBody()->write(json_encode($troncs));

    return $response;

  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }
});

/*
 * récupère le détails d'un tronc (a enrichir avec les troncs_queteurs associés)
 *
 * */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/troncs/{id}', function ($request, $response, $args)
{

  try
  {
    $ulId    = (int)$args['ul-id'];
    $troncId = (int)$args['id'];

    $troncDBService = new TroncDBService($this->db, $this->logger);

    try
    {
      $tronc = $troncDBService->getTroncById($troncId, $ulId);
      $response->getBody()->write(json_encode($tronc));
    }
    catch(Exception $e)
    {
      $this->logger->addError($e);
    }


    return $response;

  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }
});



