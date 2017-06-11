<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:35
 */

use \RedCrossQuest\BusinessService\TroncQueteurBusinessService;
use \RedCrossQuest\DBService\TroncQueteurDBService;
use \RedCrossQuest\DBService\QueteurDBService     ;
use \RedCrossQuest\DBService\PointQueteDBService  ;
use \RedCrossQuest\DBService\TroncDBService       ;

use \RedCrossQuest\Entity\TroncQueteurEntity;

include_once("../../src/DBService/TroncQueteurDBService.php");
include_once("../../src/DBService/QueteurDBService.php");
include_once("../../src/DBService/PointQueteDBService.php");
include_once("../../src/DBService/TroncDBService.php");
include_once("../../src/BusinessService/TroncQueteurBusinessService.php");

/********************************* TRONC_QUETEUR ****************************************/

/**
 * Supprime les tronc_queteurs qui implique le tronc ({id}) et qui ont soit la colonne départ ou retour de null.
 */
$app->delete('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId    = (int)$args['ul-id'];
    //c'est bien le troncId qu'on passe ici, on va supprimer tout les tronc_queteur qui ont ce tronc_id et départ ou retour à nulle
    $troncId = (int)$args['id'];

    $troncQueteurDBService = new TroncQueteurDBService($this->db, $this->logger);
    $troncQueteurDBService->deleteNonReturnedTroncQueteur($troncId, $ulId);
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
$app->post('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $params = $request->getQueryParams();

    $troncQueteurDBService = new TroncQueteurDBService($this->db, $this->logger);

    if(array_key_exists('action', $params))
    {
      $action = $params['action'];
      $input  = $request->getParsedBody();

      $tq = new TroncQueteurEntity($input, $this->logger);

      if ($action =="saveReturnDate")
      {
        $this->logger->debug("Saving return date",[$tq]);
        $troncQueteurDBService->updateRetour($tq, $ulId);
      }
      elseif ($action =="saveCoins")
      {
        $this->logger->debug("Saving Coins");
        $troncQueteurDBService->updateCoinsCount($tq, $ulId);
      }
      elseif ($action =="saveCreditCard")
      {
        $this->logger->debug("Saving CreditCard");
        $troncQueteurDBService->updateCreditCardCount($tq, $ulId);
      }
      elseif ($action =="saveAsAdmin")
      {
        $this->logger->debug("Saving As Admin", [ $input ]);
        $troncQueteurDBService->updateTroncQueteurAsAdmin($tq, $ulId);
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
 * créer un départ théorique de tronc (insertion du tronc_queteur) (id_queteur, id_tronc, départ_théorique, point_quete)
 *
 * ou met à jour le tronc avec la date de départ. Si la date de départ est déjà mise à jour,
 * departAlreadyRegistered=true est initialisé dans tronc_queteur qui est retourné
 *
 * autoriser pour role >2
 *
 */
$app->post('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $params = $request->getQueryParams();
    $troncQueteurDBService = new TroncQueteurDBService($this->db, $this->logger);

    if(array_key_exists('action', $params))
    {
      $action   = $params['action'  ];
      $tronc_id = $params['tronc_id'];

      $troncQueteurBusinessService = new TroncQueteurBusinessService( $this->logger,
        $troncQueteurDBService,
        new QueteurDBService   ($this->db, $this->logger),
        new PointQueteDBService($this->db, $this->logger),
        new TroncDBService     ($this->db, $this->logger)
      );

      if($action == "getTroncQueteurForTroncIdAndSetDepart")
      {
        $troncQueteur = $troncQueteurBusinessService->getLastTroncQueteurFromTroncId($tronc_id, $ulId );
        if($troncQueteur->depart == null)
        {
          $departDate = $troncQueteurDBService->setDepartToNow($troncQueteur->id, $ulId );
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
    { // préparation du tronc

      $input  = $request->getParsedBody();
      $tq = new TroncQueteurEntity($input, $this->logger);

      try
      {
        $troncQueteurDBService->insert($tq, $ulId );
      }
      catch(Exception $e)
      {
        $this->logger->addError($e);
        throw $e;
      }
      return $response;
    }

  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }

});

/**
 * Recherche un tronc_queteur
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];

    $params = $request->getQueryParams();

    if(array_key_exists('action', $params))
    {
      $action   = $params['action'  ];
      $troncQueteurDBService = new TroncQueteurDBService($this->db, $this->logger);
      $troncQueteurBusinessService = new TroncQueteurBusinessService($this->logger,
        $troncQueteurDBService,
        new QueteurDBService     ($this->db, $this->logger),
        new PointQueteDBService  ($this->db, $this->logger),
        new TroncDBService       ($this->db, $this->logger));

      if($action == "getTroncQueteurForTroncId")
      {
        $this->logger->debug("action='getTroncQueteurForTroncId'");
        $tronc_id     = $params['tronc_id'];
        $troncQueteur = $troncQueteurBusinessService->getLastTroncQueteurFromTroncId($tronc_id, $ulId);
        $response->getBody()->write(json_encode($troncQueteur, JSON_NUMERIC_CHECK));
        return $response;
      }
      else if($action == "getTroncsQueteurForTroncId")
      {
        $this->logger->debug("action='getTroncsQueteurForTroncId'");
        $tronc_id     = $params['tronc_id'];
        $troncQueteur = $troncQueteurDBService->getTroncsQueteurByTroncId($tronc_id, $ulId);
        $response->getBody()->write(json_encode($troncQueteur, JSON_NUMERIC_CHECK));
        return $response;
      }
      else if($action == "getTroncsOfQueteur")
      {
        $this->logger->debug("action='getTroncsOfQueteur'");
        $queteur_id             = $params['queteur_id'];
        $troncQueteurDBService  = new TroncQueteurDBService($this->db, $this->logger);
        $troncsQueteur          = $troncQueteurDBService->getTroncsQueteur($queteur_id, $ulId);

        $response->getBody()->write(json_encode($troncsQueteur, JSON_NUMERIC_CHECK));

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


/**
 * récupère un tronc_queteur par son id
 *
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId           = (int)$args['ul-id'];
    $troncQueteurId = (int)$args['id'];
    $this->logger->debug("Getting tronc_queteur with id '".$troncQueteurId."'");

    $troncQueteurAction = new TroncQueteurBusinessService($this->logger,
      new TroncQueteurDBService($this->db, $this->logger),
      new QueteurDBService     ($this->db, $this->logger),
      new PointQueteDBService  ($this->db, $this->logger),
      new TroncDBService       ($this->db, $this->logger));

    $troncQueteur = $troncQueteurAction->getTroncQueteurFromTroncQueteurId($troncQueteurId, $ulId);

    $response->getBody()->write(json_encode($troncQueteur, JSON_NUMERIC_CHECK));

    return $response;
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }


});
