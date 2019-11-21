<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\mailing\ConfirmOpeningOfSpotfireThanksDashboard;
use RedCrossQuest\routes\routesActions\mailing\GetMailingSummary;
use RedCrossQuest\routes\routesActions\mailing\SendABatchOfMailing;

/**
 * Get summary info about mailing
 *
 * Dispo pour le role admin local
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/mailing', GetMailingSummary::class);

/**
 * Send a batch of mailing
 */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/mailing', SendABatchOfMailing::class);

/**
 * When a queteur open the thanks email from RCQ that contains a link to Spotfire Dashboard.
 * The spotfire dashboard will call this method to record the fact the dashboard has been opened by the queteur
 */
$app->post(getPrefix().'/thanks_mailing/{guid}', ConfirmOpeningOfSpotfireThanksDashboard::class);



