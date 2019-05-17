<?php

/********************************* Application Settings Exposed to GUI ****************************************/

require '../../vendor/autoload.php';

use \RedCrossQuest\Service\Logger;
use \RedCrossQuest\Entity\LoggingEntity;
/**
 * Search for Unité Locale.
 * Only for super admin
 */
$app->get(getPrefix().'/{role-id:[9]}/ul', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  Logger::dataForLogging(new LoggingEntity($decodedToken));
  try
  {
    $roleId = $decodedToken->getRoleId();
    $params = $request->getQueryParams();

    if(array_key_exists('q',$params) && $roleId == 9)
    {
      $query = $this->clientInputValidator->validateString("q", $params['q'], 50 , true );
      $uls   = $this->uniteLocaleDBService->searchUniteLocale($query);
      $response->getBody()->write(json_encode($uls));
    }
    else
    {
      $response->getBody()->write(json_encode([]));
    }

    return $response;
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while searching for UniteLocale ", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});




