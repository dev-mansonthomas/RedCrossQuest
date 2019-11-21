<?php

/********************************* Application Settings Exposed to GUI ****************************************/
require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\settings\GetAllULSettings;
use RedCrossQuest\routes\routesActions\settings\GetULSettings;
use RedCrossQuest\routes\routesActions\settings\GetULSetupStatus;
use RedCrossQuest\routes\routesActions\settings\UpdateRedQuestSettings;
use RedCrossQuest\routes\routesActions\settings\UpdateULSettings;


$app->get(getPrefix().'/{role-id:[4-9]}/settings/ul/{ul-id}', GetULSettings::class);

$app->get(getPrefix().'/{role-id:[1-9]}/settings/ul/{ul-id}/getSetupStatus', GetULSetupStatus::class);

$app->get(getPrefix().'/{role-id:[4-9]}/settings/ul/{ul-id}/getAllSettings', GetAllULSettings::class);

$app->put(getPrefix().'/{role-id:[4-9]}/settings/ul/{ul-id}/updateUL', UpdateULSettings::class);

$app->put(getPrefix().'/{role-id:[4-9]}/settings/ul/{ul-id}/updateRedQuestSettings',  UpdateRedQuestSettings::class);

