<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use \RedCrossQuest\Entity\YearlyGoalEntity;
use \RedCrossQuest\Service\Logger;
use \RedCrossQuest\Entity\LoggingEntity;

/********************************* QUETEUR ****************************************/


/**
 * Fetch the yearly goal of an UL
 *
 * Dispo pour le role admin local
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  Logger::dataForLogging(new LoggingEntity($decodedToken));
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

    $yearlyGoal = $this->yearlyGoalDBService->getYearlyGoals($ulId, $year);

    if($yearlyGoal != null)
    {
      $response->getBody()->write(json_encode($yearlyGoal));
    }


    return $response;
  }
  catch(\Exception $e)
  {
    $this->logger->error("fetch the dailyStats for a year($year)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }

});



/**
 *
 * Update goal and it's split across days for an UL
 *
 */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  Logger::dataForLogging(new LoggingEntity($decodedToken));
  try
  {
    $ulId             = $decodedToken->getUlId  ();
    $input            = $request->getParsedBody();
    $yearlyGoalEntity = new YearlyGoalEntity($input, $this->logger);

    $this->yearlyGoalDBService->update($yearlyGoalEntity, $ulId);
  }
  catch(\Exception $e)
  {
    $this->logger->error("Update one day stats ", array('decodedToken'=>$decodedToken, "Exception"=>$e, "dailyStatsBeforeRCQEntity"=>$yearlyGoalEntity));
    throw $e;
  }
  return $response;
});



/**
 * Create goals for a year
 */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  Logger::dataForLogging(new LoggingEntity($decodedToken));
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $input  = $request->getParsedBody();
    $year   = $this->clientInputValidator->validateInteger('year', getParam($input,'year'), 2050, true);

    $this->yearlyGoalDBService->createYear($ulId, $year);
  }
  catch(\Exception $e)
  {
    $this->logger->error("error while creating year (".$input['year'].")", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
  return $response;
});


