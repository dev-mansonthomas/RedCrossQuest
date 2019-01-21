<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use \RedCrossQuest\DBService\YearlyGoalDBService;
use \RedCrossQuest\Entity\YearlyGoalEntity;

include_once("../../src/Entity/YearlyGoalEntity.php");
/********************************* QUETEUR ****************************************/


/**
 * Fetch the yearly goal of an UL
 *
 * Dispo pour le role admin local
 */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals', function ($request, $response, $args)
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

    $yearlyGoalDBService = new YearlyGoalDBService($this->db, $this->logger);
    //$this->logger->addInfo("DailyStats list - UL ID '".$ulId."'' role ID : $roleId");
    $yearlyGoal = $yearlyGoalDBService->getYearlyGoals($ulId, $year);

    if($yearlyGoal != null)
    {
      $response->getBody()->write(json_encode($yearlyGoal));
    }


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
 * Update goal and it's split across days for an UL
 *
 */
$app->put('/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals/{id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = (int)$args['ul-id'];

    $yearlyGoalDBService = new YearlyGoalDBService($this->db, $this->logger);
    $input               = $request->getParsedBody();
    $yearlyGoalEntity    = new YearlyGoalEntity($input);
    
    $yearlyGoalDBService->update($yearlyGoalEntity, $ulId);
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Update one day stats ", array('decodedToken'=>$decodedToken, "Exception"=>$e, "dailyStatsBeforeRCQEntity"=>$yearlyGoalEntity));
    throw $e;
  }
  return $response;
});



/**
 * Create goals for a year
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId = (int)$args['ul-id'];

    $yearlyGoalDBService = new YearlyGoalDBService($this->db, $this->logger);
    $input               = $request->getParsedBody();
    $yearlyGoalDBService->createYear($ulId, $input['year']);
  }
  catch(\Exception $e)
  {
    $this->logger->addError("error while creating year (".$input['year'].")", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
  return $response;
});


