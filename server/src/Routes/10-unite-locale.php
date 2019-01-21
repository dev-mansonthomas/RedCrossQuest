<?php

/********************************* Application Settings Exposed to GUI ****************************************/

use \RedCrossQuest\DBService\UniteLocaleDBService;


/**
 * Search for Unité Locale.
 * Only for super admin
 */
$app->get('/{role-id:[9]}/ul', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $roleId = (int)$args['role-id'];
    $params = $request->getQueryParams();

    if(array_key_exists('q',$params))
    {
      $ulDBservice = new UniteLocaleDBService($this->db, $this->logger);
      $query = $params['q'];
      $uls = $ulDBservice->searchUniteLocale($query);
      $response->getBody()->write(json_encode($uls));
    }

    return $response;
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while searching for UniteLocale ", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});




