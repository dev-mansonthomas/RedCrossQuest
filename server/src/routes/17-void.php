<?php

/********************************* Application Settings Exposed to GUI ****************************************/

use RedCrossQuest\routes\routesActions\void\HtmlManagementDashboards;


/**
 * @OA\Tag(
 *   name="Void",
 *   description="Return 404 without triggering error in Slack/logs for annoying repetitive query"
 * )
 */

/**
 *
 * @OA\Get(
 *     path="/html/management/dashboards",
 *     tags={"Void"},
 *     summary="Search UL by Name/PostalCode/City",
 *     description="Search UL by Name/PostalCode/City",
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/html/management/dashboards', HtmlManagementDashboards::class);
