<?php
// Routes


$app->get('/troncs', function ($request, $response, $args) {
  $this->logger->addInfo("Troncs list");
  
  $mapper = new RedCrossQuest\TroncMapper($this->db, $this->logger);
  $params = $request->getQueryParams();

  if(array_key_exists('searchType',$params))
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
});


$app->get('/troncs/{id}', function ($request, $response, $args) {
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
});


$app->get('/queteurs', function ($request, $response, $args) {
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
});


$app->get('/queteurs/{id}', function ($request, $response, $args) {
    $queteurId = (int)$args['id'];
    $mapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);
    $queteur = $mapper->getQueteurById($queteurId);

    try
    {
      $response->getBody()->write(json_encode($queteur));
    }
    catch(Exception $e)
    {
      $this->logger->addError($e);
    }


    return $response;
});


$app->put('/queteurs/{id}', function ($request, $response, $args)
{
  $mapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);
  $input = $request->getParsedBody();
  $queteur = new \RedCrossQuest\QueteurEntity($input);
  try
  {
    $mapper->update($queteur);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});

$app->post('/queteurs', function ($request, $response, $args)
{
  $mapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);
  $input = $request->getParsedBody();

  $queteur = new \RedCrossQuest\QueteurEntity($input);
  $this->logger->error("queteurs", [$queteur]);
  try
  {
    $mapper->insert($queteur);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});





/*
$app->get('/[{name}]', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Slim-Skeleton '/' route");

  // Render index view
  return $this->renderer->render($response, 'index.phtml', $args);
});
  */
