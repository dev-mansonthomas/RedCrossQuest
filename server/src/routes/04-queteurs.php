<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */


/********************************* QUETEUR ****************************************/


/**
 * récupère les queteurs
 */
$app->get('/{role-id:[1-9]+}/ul/{ul-id}/queteurs', function ($request, $response, $args)
{

  try
  {
    $ulId   = (int)$args['ul-id'];
    $roleId = (int)$args['role-id'];

    $this->logger->addInfo("Request UL ID '".$ulId."'' role ID : $roleId");
    
    $this->logger->addInfo("Queteur list");

    $mapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);
    $params = $request->getQueryParams();

    if(array_key_exists('q',$params))
    {
      $this->logger->addInfo("Queteur with query string '".$params['q']."''");
      $queteurs = $mapper->getQueteurs($params['q']);
    }
    else if(array_key_exists('searchType',$params))
    {
      $this->logger->addInfo("Queteur by search type '".$params['searchType']."''");
      $queteurs = $mapper->getQueteursBySearchType($params['searchType']);
    }
    else
    {
      $this->logger->addInfo("Queteur by search type defaulted to type='0'");
      $queteurs = $mapper->getQueteursBySearchType(0);
    }


    $response->getBody()->write(json_encode($queteurs));

    return $response;
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }

});


/**
 * Récupère un queteur
 */
$app->get('/ul/{ul-id}/queteurs/{id}', function ($request, $response, $args)
{

  try
  {
    $ulId = (int)$args['ul-id'];

    $this->logger->addInfo("Request UL ID '".$ulId."''");

    $queteurId = (int)$args['id'];
    $mapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);
    $queteur = $mapper->getQueteurById($queteurId);
    $response->getBody()->write(json_encode($queteur));
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }


  return $response;
});


/**
 * update un queteur
 */
$app->put('/ul/{ul-id}/queteurs/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];

    $this->logger->addInfo("Request UL ID '".$ulId."''");

    $mapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);
    $input = $request->getParsedBody();
    $queteur = new \RedCrossQuest\QueteurEntity($input);
    $mapper->update($queteur);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});



/**
 * Crée un nouveau queteur
 */
$app->post('/ul/{ul-id}/queteurs', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];

    $this->logger->addInfo("Request UL ID '".$ulId."''");
    $mapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);
    $input = $request->getParsedBody();

    $queteur = new \RedCrossQuest\QueteurEntity($input);
    $this->logger->error("queteurs", [$queteur]);
    $mapper->insert($queteur);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});


