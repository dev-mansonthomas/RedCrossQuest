<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use \RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use \RedCrossQuest\Entity\DailyStatsBeforeRCQEntity;

include_once("../../src/Entity/DailyStatsBeforeRCQEntity.php");
/********************************* QUETEUR ****************************************/


/**
 * récupère les données de quête pour une année donnée
 *
 * Dispo pour le role admin local
 */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/dailyStats', function ($request, $response, $args)
{

  try
  {
    $ulId   = (int)$args['ul-id'];
    $roleId = (int)$args['role-id'];

    $params = $request->getQueryParams();

    if(array_key_exists('year',$params))
    {
      $year = $params['year'];
    }
    else
    {
      $year =  date("Y");
    }

    $dailyStatsBeforeRCQDBService = new DailyStatsBeforeRCQDBService($this->db, $this->logger);
    $this->logger->addInfo("DailyStats list - UL ID '".$ulId."'' role ID : $roleId");
    $dailyStats = $dailyStatsBeforeRCQDBService->getDailyStats($ulId, $year);

    $response->getBody()->write(json_encode($dailyStats));

    return $response;
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }

});



/**
 *
 * Mise à jour du montant d'une entrée DailyStats
 *
 */
$app->put('/{role-id:[5-9]}/ul/{ul-id}/dailyStats/{id}', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];

    $dailyStatsBeforeRCQDBService = new DailyStatsBeforeRCQDBService($this->db, $this->logger);
    $input            = $request->getParsedBody();
    $dailyStatsBeforeRCQEntity    = new DailyStatsBeforeRCQEntity($input);
    
    $dailyStatsBeforeRCQDBService->update($dailyStatsBeforeRCQEntity, $ulId);
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
$app->post('/{role-id:[5-9]}/ul/{ul-id}/dailyStats', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];

    $dailyStatsBeforeRCQDBService = new DailyStatsBeforeRCQDBService($this->db, $this->logger);
    $input                        = $request->getParsedBody();
    $dailyStatsBeforeRCQDBService->createYear($ulId, $input['year']);
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
  }
  return $response;
});


