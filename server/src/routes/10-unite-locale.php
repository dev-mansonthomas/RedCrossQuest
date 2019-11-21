<?php

/********************************* Application Settings Exposed to GUI ****************************************/

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\uniteLocale\GetUniteLocale;
use RedCrossQuest\routes\routesActions\uniteLocale\ListUniteLocale;


$app->get(getPrefix().'/{role-id:[9]}/ul/:id', GetUniteLocale::class);

/**
 * Search for Unité Locale.
 * Only for super admin
 */
$app->get(getPrefix().'/{role-id:[9]}/ul', ListUniteLocale::class);




