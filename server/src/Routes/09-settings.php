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
    $roleId = (int)$args['role-id'];

    if(array_key_exists('action', $params))
    {
      $action   = $params['action'  ];


      if($action == "getSetupStatus")
      {
        $response->getBody()->write(json_encode($this->settingsBusinessService->getSetupStatus($decodedToken->getUlId())));
        return $response;
      }
    }
    else
    {
      $guiSettings['mapKey'] = $this->settings['appSettings']['gmapAPIKey'];
      $guiSettings['ul'    ] = $this->uniteLocaleDBService->getUniteLocaleById   ($decodedToken->getUlId());
      $guiSettings['user'  ] = $this->userDBService       ->getUserInfoWithUserId($decodedToken->getUid(), $decodedToken->getUlId(), $roleId);

      $response->getBody()->write(json_encode($guiSettings));
      return $response;
    }

  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while getting settings", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



