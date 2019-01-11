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
 * Fetch the daily stats of an UL
 *
 * Dispo pour le role admin local
 */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/dailyStats', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = (int)$args['ul-id'];

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
    //$this->logger->addInfo("DailyStats list - UL ID '".$ulId."'' role ID : $roleId");
    $dailyStats = $dailyStatsBeforeRCQDBService->getDailyStats($ulId, $year);

    if($dailyStats !== null && count($dailyStats) > 0)
    {
      $this->logger->addInfo($dailyStats[0]->generateCSVHeader());
      $this->logger->addInfo($dailyStats[0]->generateCSVRow());

    }
    $response->getBody()->write(json_encode($dailyStats));

    return $response;
  }
  catch(\Exception $e)
  {
    $this->logger->addError("fetch the dailyStats for a year($year)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }

});



/**
 *
 * Update amount of money collected for one day of one year of the current Unite Locale
 *
 */
$app->put('/{role-id:[4-9]}/ul/{ul-id}/dailyStats/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = (int)$args['ul-id'];

    $dailyStatsBeforeRCQDBService = new DailyStatsBeforeRCQDBService($this->db, $this->logger);
    $input                        = $request->getParsedBody();
    $dailyStatsBeforeRCQEntity    = new DailyStatsBeforeRCQEntity($input, $this->logger);
    
    $dailyStatsBeforeRCQDBService->update($dailyStatsBeforeRCQEntity, $ulId);
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Update one day stats ", array('decodedToken'=>$decodedToken, "Exception"=>$e, "dailyStatsBeforeRCQEntity"=>$dailyStatsBeforeRCQEntity));
    throw $e;
  }
  return $response;
});



/**
 * Creation of all days for a year for an UL)
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/dailyStats', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = (int)$args['ul-id'];

    $dailyStatsBeforeRCQDBService = new DailyStatsBeforeRCQDBService($this->db, $this->logger);
    $input                        = $request->getParsedBody();
    $dailyStatsBeforeRCQDBService->createYear($ulId, $input['year']);
  }
  catch(\Exception $e)
  {
    $this->logger->addError("error while creating year (".$input['year'].")", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
  return $response;
});


