<?php

/********************************* Application Settings Exposed to GUI ****************************************/

use RedCrossQuest\routes\routesActions\void\VoidAction;


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
/** @noinspection PhpUndefinedVariableInspection */

$app->get ('/html[/{params:.*}]'      , VoidAction::class);
$app->get ('/api[/{params:.*}]'       , VoidAction::class);
$app->get ('/V1[/{params:.*}]'        , VoidAction::class);
$app->get ('/v1[/{params:.*}]'        , VoidAction::class);
$app->get ('/sharelinks[/{params:.*}]', VoidAction::class);
$app->get ('/menu[/{params:.*}]'      , VoidAction::class);
$app->get ('/domains[/{params:.*}]'   , VoidAction::class);
$app->get ('/issueNav[/{params:.*}]'  , VoidAction::class);
$app->get ('/config[/{params:.*}]'    , VoidAction::class);
$app->get ('/.env'                    , VoidAction::class);
$app->get ('/resetPassword'           , VoidAction::class);
$app->post('/tinymce[/{params:.*}]' , VoidAction::class);
