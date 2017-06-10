<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:33
 */

use \RedCrossQuest\DBService\TroncDBService;
use \RedCrossQuest\Entity\TroncEntity;

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

    $active     = array_key_exists('active'     ,$params)?$params['active'    ]:1;
    

    if(array_key_exists('q',$params))
    {
      $this->logger->addInfo("Tronc by search type '".$params['q']."''");
      $troncs = $troncDBService->getTroncs($params['q'], $ulId, $active );
    }
    else if(array_key_exists('searchType',$params))
    {
      $this->logger->addInfo("Tronc by search type '".$params['searchType']."''");
      $troncs = $troncDBService->getTroncsBySearchType($params['searchType'], $ulId);
    }
    else
    {
      $troncs = $troncDBService->getTroncs(null,$ulId, $active );
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

    $tronc = $troncDBService->getTroncById($troncId, $ulId);
    $response->getBody()->write(json_encode($tronc));

    return $response;

  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }
});



/*
 * Update le tronc, seulement pour l'admin
 *
 * */
$app->put('/{role-id:[4-9]}/ul/{ul-id}/troncs/{id}', function ($request, $response, $args)
{

  try
  {
    $ulId    = (int)$args['ul-id'];

    $troncDBService = new TroncDBService($this->db, $this->logger);

    $input            = $request->getParsedBody();
    $troncEntity      = new TroncEntity($input);

    $troncDBService->update($troncEntity, $ulId);
    return ;

  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }
});



