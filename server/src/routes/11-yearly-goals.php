<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\yearlyGoals\CreateYearlyGoals;
use RedCrossQuest\routes\routesActions\yearlyGoals\ListYearlyGoals;
use RedCrossQuest\routes\routesActions\yearlyGoals\UpdateYearlyGoals;

/********************************* QUETEUR ****************************************/


/**
 * Fetch the yearly goal of an UL
 *
 * Dispo pour le role admin local
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals', ListYearlyGoals::class);

/**
 *
 * Update goal and it's split across days for an UL
 *
 */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals/{id}', UpdateYearlyGoals::class);


/**
 * Create goals for a year
 */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals', CreateYearlyGoals::class);
