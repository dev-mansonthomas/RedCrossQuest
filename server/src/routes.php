<?php
// Routes

/********************************* TRONC ****************************************/
$app->get('/troncs', function ($request, $response, $args) {
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


/********************************* TRONC_QUETEUR ****************************************/

/**
 * Supprime les tronc_queteurs qui implique le tronc ({id}) et qui ont soit la colonne départ ou retour de null.
 */
$app->delete('/tronc_queteur/{id}', function ($request, $response, $args)
{
  $mapper = new RedCrossQuest\TroncQueteurMapper($this->db, $this->logger);
  //c'est bien le troncId qu'on passe ici, on va supprimer tout les tronc_queteur qui ont ce tronc_id et départ ou retour à nulle
  $troncId = (int)$args['id'];

  try
  {
    $mapper->deleteNonReturnedTroncQueteur($troncId);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }
});


/**
 * créer un départ de tronc (id_queteur, id_tronc, départ_théorique, point_quete)
 *
 */
$app->post('/tronc_queteur/{id}', function ($request, $response, $args)
{
  try
  {
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

/**
 * créer un départ de tronc (id_queteur, id_tronc, départ_théorique, point_quete)
 *
 */
$app->post('/tronc_queteur', function ($request, $response, $args)
{
  $params = $request->getQueryParams();
  $troncQueteurMapper = new RedCrossQuest\TroncQueteurMapper($this->db, $this->logger);

  if(array_key_exists('action', $params))
  {
    $action   = $params['action'  ];
    $tronc_id = $params['tronc_id'];

    $troncQueteurAction = new RedCrossQuest\TroncQueteurAction($this->logger,
                                                                $troncQueteurMapper,
                                                                new RedCrossQuest\QueteurMapper                   ($this->db, $this->logger),
                                                                new RedCrossQuest\PointQueteMapper                ($this->db, $this->logger));


    if($action == "getTroncQueteurForTroncIdAndSetDepart")
    {
      $troncQueteur = $troncQueteurAction->getLastTroncQueteurFromTroncId($tronc_id);

      if($troncQueteur->depart == null)
      {
        $departDate = $troncQueteurMapper->setDepartToNow($troncQueteur->id);
        $troncQueteur->depart = $departDate;
      }
      else
      {
        $troncQueteur->departAlreadyRegistered=true;
        $this->logger->warn("TroncQueteur with id='".$troncQueteur->id."' has already a 'depart' defined('".$troncQueteur->depart."'), don't update it");
      }

      $response->getBody()->write(json_encode($troncQueteur));
      return $response;
    }

  }
  else
  {

    $input  = $request->getParsedBody();

    $tq = new \RedCrossQuest\TroncQueteurEntity($input, $this->logger);
    $this->logger->debug("tronc_queteur", [$tq]);
    try
    {
      $troncQueteurMapper->insert($tq);
    }
    catch(Exception $e)
    {
      $this->logger->addError($e);
      throw $e;
    }
    return $response;
  }

});

/**
 * Recherche un tronc_queteur
 */
$app->get('/tronc_queteur', function ($request, $response, $args)
{
  try
  {

    $params = $request->getQueryParams();

    if(array_key_exists('action', $params))
    {
      $action   = $params['action'  ];
      $tronc_id = $params['tronc_id'];

      $troncQueteurAction = new RedCrossQuest\TroncQueteurAction($this->logger,
                                                                  new RedCrossQuest\TroncQueteurMapper($this->db, $this->logger),
                                                                  new RedCrossQuest\QueteurMapper($this->db, $this->logger),
                                                                  new RedCrossQuest\PointQueteMapper($this->db, $this->logger));

      if($action == "getTroncQueteurForTroncId")
      {
        $troncQueteur = $troncQueteurAction->getLastTroncQueteurFromTroncId($tronc_id);
        $response->getBody()->write(json_encode($troncQueteur, JSON_NUMERIC_CHECK));
        return $response;
      }
    }

  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }


  return $response;
});


/********************************* POINT_QUETE ****************************************/

$app->get('/pointQuetes', function ($request, $response, $args) {
  $this->logger->addInfo("Point Quete list");

  $mapper = new RedCrossQuest\PointQueteMapper($this->db, $this->logger);
  $params = $request->getQueryParams();

  $this->logger->addInfo("PointQuetes query");
  $pointQuetes = $mapper->getPointQuetes();


  $response->getBody()->write(json_encode($pointQuetes));

  return $response;
});

/********************************* QUETEUR ****************************************/


/**
 * récupère les queteurs
 */
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


/**
 * Récupère un queteur
 */
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


/**
 * update un queteur
 */
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



/**
 * Crée un nouveau queteur
 */
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
