<?php
// Routes





$app->get('/queteurs', function ($request, $response, $args) {
  $this->logger->addInfo("Queteur list");
  $mapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);

  $params = $request->getQueryParams();
  if(array_key_exists('q',$params))
  {
    $queteurs = $mapper->getQueteurs($params['q']);
  }
  else
  {
    $queteurs = $mapper->getQueteurs(null);
  }


  $response->getBody()->write(json_encode($queteurs));

  return $response;
});


$app->get('/queteurs/{id}', function ($request, $response, $args) {
    $queteurId = (int)$args['id'];
    $mapper = new RedCrossQuest\QueteurMapper($this->db);
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
  $mapper = new RedCrossQuest\QueteurMapper($this->db);
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





/*
$app->get('/[{name}]', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Slim-Skeleton '/' route");

  // Render index view
  return $this->renderer->render($response, 'index.phtml', $args);
});
  */
