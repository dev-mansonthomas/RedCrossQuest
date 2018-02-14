<?php

/********************************* Application Settings Exposed to GUI ****************************************/

use \RedCrossQuest\BusinessService\SettingsBusinessService;
use \RedCrossQuest\DBService\UserDBService;
use \RedCrossQuest\DBService\QueteurDBService;
use \RedCrossQuest\DBService\PointQueteDBService;
use \RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use \RedCrossQuest\DBService\TroncDBService;


/**
 * get the google maps API Key
 */
$app->get('/{role-id:[1-9]}/settings/ul/{ul-id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $params = $request->getQueryParams();

    if(array_key_exists('action', $params))
    {
      $action   = $params['action'  ];


      if($action == "getSetupStatus")
      {
        $settingsBusinessService = new SettingsBusinessService(
          $this->logger,
          new QueteurDBService              ($this->db, $this->logger),
          new UserDBService                 ($this->db, $this->logger),
          new PointQueteDBService           ($this->db, $this->logger),
          new DailyStatsBeforeRCQDBService  ($this->db, $this->logger),
          new TroncDBService                ($this->db, $this->logger)
        );

        $response->getBody()->write(json_encode($settingsBusinessService->getSetupStatus($decodedToken->getUlId())));
        return $response;
      }
    }
    else
    {
      $phpSettings = $this->get('settings');

      $guiSettings['mapKey'] = $phpSettings['appSettings']['gmapAPIKey'];

      $response->getBody()->write(json_encode($guiSettings));
      return $response;
    }




  }
  catch(Exception $e)
  {
    $this->logger->addError("Error while getting google maps API Key ", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



