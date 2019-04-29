<?php

/********************************* Application Settings Exposed to GUI ****************************************/

use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use \RedCrossQuest\Entity\UniteLocaleEntity;

require '../../vendor/autoload.php';




/**
 * get the application settings
 */
$app->get(getPrefix().'/{role-id:[1-9]}/settings/ul/{ul-id}', function ($request, $response, $args)
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
      else if($action == "getAllSettings")
      {
        $guiSettings['mapKey'        ] = $this->settings['appSettings']['gmapAPIKey'     ];
        $guiSettings['RGPDVideo'     ] = $this->settings['appSettings']['RGPDVideo'      ];
        $guiSettings['RedQuestDomain'] = $this->settings['appSettings']['RedQuestDomain' ];
        $guiSettings['ul'            ] = $this->uniteLocaleDBService         ->getUniteLocaleById   ($ulId);
        $guiSettings['ul_settings'   ] = $this->uniteLocaleSettingsDBService ->getUniteLocaleById   ($ulId);
        $guiSettings['user'          ] = $this->userDBService                ->getUserInfoWithUserId($userId, $ulId, $roleId);
        $guiSettings['RCQVersion'    ] = $this->RCQVersion;
        $guiSettings['FirstDay'      ] = DailyStatsBeforeRCQDBService::getCurrentQueteStartDate();

        return $response->getBody()->write(json_encode($guiSettings));
      }
    }
    else
    {
      return $response->getBody()->write(json_encode($this->uniteLocaleDBService         ->getUniteLocaleById   ($ulId)));
    }
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while getting settings", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});


/**
 * get the google maps API Key
 */
$app->put(getPrefix().'/{role-id:[1-9]}/settings/ul/{ul-id}', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

  $ulId   = $decodedToken->getUlId  ();
  $roleId = $decodedToken->getRoleId();
  $userId = $decodedToken->getUid   ();
  $input  = $request->getParsedBody ();

  if($roleId < 4)
  {
    $response500 = $response->withStatus(500);
    $response500->getBody()->write(json_encode(["error"=>'internal error']));
    return $response500;
  }

  try
  {
    $ulEntity    = new UniteLocaleEntity($input, $this->logger);
    $this->uniteLocaleDBService->updateUL($ulEntity, $ulId, $userId);

  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while updating settings", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



