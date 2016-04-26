<?php
// Routes





$app->get('/queteurs', function ($request, $response, $args) {
    $this->logger->addInfo("Queteur list");
    $mapper = new RedCrossQuest\QueteurMapper($this->db);
    $queteurs = $mapper->getQueteurs();

    $response->getBody()->write(json_encode($queteurs));
    return $response;
});


$app->get('/queteurs/{id}', function ($request, $response, $args) {
    $queteurId = (int)$args['id'];
    $mapper = new RedCrossQuest\QueteurMapper($this->db);
    $queteur = $mapper->getQueteurById($queteurId);

    try
    {
      $this->logger->addError("before encode");
      $response->getBody()->write(json_encode($queteur));
      $this->logger->addError(json_last_error());
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
