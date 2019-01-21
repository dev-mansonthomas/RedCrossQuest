<?php

/********************************* Application Settings Exposed to GUI ****************************************/

require '../../vendor/autoload.php';

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
      $query = $params['q'];
      $uls   = $this->uniteLocaleDBService->searchUniteLocale($query);
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




