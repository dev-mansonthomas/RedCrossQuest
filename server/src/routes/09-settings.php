<?php

/********************************* Application Settings Exposed to GUI ****************************************/

/**
 * get the google maps API Key
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/settings', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $phpSettings = $this->get('settings');

    $guiSettings['mapKey'] = $phpSettings['appSettings']['gmapAPIKey'];

    $response->getBody()->write(json_encode($guiSettings));

    return $response;
  }
  catch(Exception $e)
  {
    $this->logger->addError("Error while getting google maps API Key ", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



