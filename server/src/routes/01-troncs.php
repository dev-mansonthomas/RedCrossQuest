<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:33
 */


/********************************* TRONC ****************************************/


/**
 récupère la liste des troncs (affichage des Troncs & QRCode Troncs)
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/troncs', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $this->logger->addInfo("Request UL ID '".$ulId."''");

    $this->logger->addInfo("Troncs list");

    $mapper = new RedCrossQuest\TroncMapper($this->db, $this->logger);
    $params = $request->getQueryParams();


    if(array_key_exists('q',$params))
    {
      $this->logger->addInfo("Tronc by search type '".$params['q']."''");
      $troncs = $mapper->getTroncs($params['q']);
    }
    else if(array_key_exists('searchType',$params))
    {
      $this->logger->addInfo("Tronc by search type '".$params['searchType']."''");
      $troncs = $mapper->getTroncsBySearchType($params['searchType']);
    }
    else
    {
      $troncs = $mapper->getTroncs();
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
    $ulId = (int)$args['ul-id'];
    $this->logger->addInfo("Request UL ID '".$ulId."''");


    $troncId = (int)$args['id'];
    $mapper = new RedCrossQuest\TroncMapper($this->db, $this->logger);
    $tronc = $mapper->getTroncById($troncId);

    try
    {
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



