<?php

/********************************* Application Settings Exposed to GUI ****************************************/

require '../../vendor/autoload.php';




/**
 * get the google maps API Key
 */
$app->get('/{role-id:[1-9]}/settings/ul/{ul-id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $params = $request->getQueryParams();
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();
    $userId = $decodedToken->getUid   ();

    if(array_key_exists('action', $params))
    {
      $action   = $this->clientInputValidator->validateString("action", $params['action'], 20 , false );

      if($action == "getSetupStatus")
      {
        return $response->getBody()->write(json_encode($this->settingsBusinessService->getSetupStatus($ulId)));
      }
    }
    else
    {
      $guiSettings['mapKey'   ] = $this->settings['appSettings']['gmapAPIKey'];
      $guiSettings['RGPDVideo'] = $this->settings['appSettings']['RGPDVideo' ];
      $guiSettings['ul'       ] = $this->uniteLocaleDBService->getUniteLocaleById   ($ulId);
      $guiSettings['user'     ] = $this->userDBService       ->getUserInfoWithUserId($userId, $ulId, $roleId);

      return $response->getBody()->write(json_encode($guiSettings));
    }
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while getting settings", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



