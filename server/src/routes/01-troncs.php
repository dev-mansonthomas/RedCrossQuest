<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:33
 */


/********************************* TRONC ****************************************/
$app->get('/ul/{ul-id}/troncs', function ($request, $response, $args)
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


$app->get('/ul/{ul-id}/troncs/{id}', function ($request, $response, $args)
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


/********************************* TRONC_QUETEUR ****************************************/

/**
 * Supprime les tronc_queteurs qui implique le tronc ({id}) et qui ont soit la colonne départ ou retour de null.
 */
$app->delete('/ul/{ul-id}/tronc_queteur/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $this->logger->addInfo("Request UL ID '".$ulId."''");

    $mapper = new RedCrossQuest\TroncQueteurMapper($this->db, $this->logger);
    //c'est bien le troncId qu'on passe ici, on va supprimer tout les tronc_queteur qui ont ce tronc_id et départ ou retour à nulle
    $troncId = (int)$args['id'];
    $mapper->deleteNonReturnedTroncQueteur($troncId);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }

});


/**
 * update troncs
 *
 */
$app->post('/ul/{ul-id}/tronc_queteur/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $this->logger->addInfo("Request UL ID '".$ulId."''");

    $params = $request->getQueryParams();
    $troncQueteurMapper = new RedCrossQuest\TroncQueteurMapper($this->db, $this->logger);

    if(array_key_exists('action', $params))
    {
      $action = $params['action'];
      $this->logger->debug("action found with value '".$action."'");

      $input  = $request->getParsedBody();

      $this->logger->debug("parsed json input : ",[$input]);
      $tq = new \RedCrossQuest\TroncQueteurEntity($input, $this->logger);

      if ($action =="saveReturnDate")
      {
        $this->logger->debug("Saving return date");
        $troncQueteurMapper->updateRetour($tq);
      }
      elseif ($action =="saveCoins")
      {
        $this->logger->debug("Saving Coins");
        $troncQueteurMapper->updateCoinsCount($tq);
      }
    }
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }


});

