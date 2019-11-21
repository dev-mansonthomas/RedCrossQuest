<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\dailyStats\CreateYearOfDailyStats;
use RedCrossQuest\routes\routesActions\dailyStats\ListDailyStats;
use RedCrossQuest\routes\routesActions\dailyStats\UpdateDailyStats;


/********************************* QUETEUR ****************************************/


/**
 * Fetch the daily stats of an UL
 *
 * Dispo pour le role admin local
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/dailyStats', ListDailyStats::class);


/**
 *
 * Update amount of money collected for one day of one year of the current Unite Locale
 *
 */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/dailyStats/{id}', UpdateDailyStats::class);

/**
 * Creation of all days for a year for an UL)
 */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/dailyStats', CreateYearOfDailyStats::class);



