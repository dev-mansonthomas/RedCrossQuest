<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use \RedCrossQuest\Entity\DailyStatsBeforeRCQEntity;

/********************************* QUETEUR ****************************************/


/**
 * Fetch the daily stats of an UL
 *
 * Dispo pour le role admin local
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/dailyStats', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $params = $request->getQueryParams();

    if(array_key_exists('year',$params))
    {
      $year = $this->clientInputValidator->validateInteger('year', $params['year'], 2050, true);
    }
    else
    {
      $year =  date("Y");
    }


    //$this->logger->info("DailyStats list - UL ID '".$ulId."'' role ID : $roleId");
    $dailyStats = $this->dailyStatsBeforeRCQDBService->getDailyStats($ulId, $year);

    if($dailyStats !== null && count($dailyStats) > 0)
    {
      $this->logger->info($dailyStats[0]->generateCSVHeader());
      $this->logger->info($dailyStats[0]->generateCSVRow   ());

    }
    return $response->getBody()->write(json_encode($dailyStats));
  }
  catch(\Exception $e)
  {
    $this->logger->error("fetch the dailyStats for a year($year)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }

});

/**
 *
 * Update amount of money collected for one day of one year of the current Unite Locale
 *
 */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/dailyStats/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId                         = $decodedToken->getUlId ();
    $input                        = $request->getParsedBody();
    $dailyStatsBeforeRCQEntity    = new DailyStatsBeforeRCQEntity($input, $this->logger);

    $this->dailyStatsBeforeRCQDBService->update($dailyStatsBeforeRCQEntity, $ulId);
  }
  catch(\Exception $e)
  {
    $this->logger->error("Update one day stats ", array('decodedToken'=>$decodedToken, "Exception"=>$e, "dailyStatsBeforeRCQEntity"=>$dailyStatsBeforeRCQEntity));
    throw $e;
  }
  return $response;
});

/**
 * Creation of all days for a year for an UL)
 */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/dailyStats', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId  = $decodedToken->getUlId ();
    $input = $request->getParsedBody();
    $year  = $this->clientInputValidator->validateInteger('year', getParam($input, 'year'), 2050, true);

    $this->dailyStatsBeforeRCQDBService->createYear($ulId, $year);
  }
  catch(\Exception $e)
  {
    $this->logger->error("error while creating year ($year)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
  return $response;
});


