<?php

/********************************* Application Settings Exposed to GUI ****************************************/

use \RedCrossQuest\BusinessService\SettingsBusinessService;
use \RedCrossQuest\DBService\UserDBService;
use \RedCrossQuest\DBService\UniteLocaleDBService;
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
    $roleId = (int)$args['role-id'];

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

      $ulDBservice   = new UniteLocaleDBService($this->db, $this->logger);
      $userDBservice = new UserDBService       ($this->db, $this->logger);

      $guiSettings['mapKey'] = $phpSettings['appSettings']['gmapAPIKey'];

      $ul       = $ulDBservice  ->getUniteLocaleById   ($decodedToken->getUlId());
      $userInfo = $userDBservice->getUserInfoWithUserId($decodedToken->getUid(), $decodedToken->getUlId(), $roleId);

      $guiSettings['ul'  ] = $ul;
      $guiSettings['user'] = $userInfo;

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



