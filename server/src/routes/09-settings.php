<?php

/********************************* Application Settings Exposed to GUI ****************************************/
require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\settings\GetAllULSettings;
use RedCrossQuest\routes\routesActions\settings\GetULSettings;
use RedCrossQuest\routes\routesActions\settings\GetULSetupStatus;
use RedCrossQuest\routes\routesActions\settings\UpdateRedCrossQuestSettings;
use RedCrossQuest\routes\routesActions\settings\UpdateRedQuestSettings;


/**
 * @OA\Tag(
 *   name="RCQSettings",
 *   description="Methods related to the settings of one UniteLocal in RCQ."
 * )
 */

/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/settings/ul/{ul-id}",
 *     tags={"RCQSettings"},
 *     summary="Get the UL details",
 *     description="Get the UL details",
 *    @OA\Parameter(
 *         name="role-id",
 *         in="path",
 *         description="Current User Role",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="ul-id",
 *         in="path",
 *         description="User's Unite Locale ID",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="object",
 *          ref="#/components/schemas/GetULSettingsResponse",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[4-9]}/settings/ul/{ul-id}', GetULSettings::class);

/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/settings/ul/{ul-id}/getSetupStatus",
 *     tags={"RCQSettings"},
 *     summary="Get the UL Setup Status",
 *     description="Get the UL Setup Status",
 *    @OA\Parameter(
 *         name="role-id",
 *         in="path",
 *         description="Current User Role",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="ul-id",
 *         in="path",
 *         description="User's Unite Locale ID",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="object",
 *          ref="#/components/schemas/GetULSetupStatusResponse",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/settings/ul/{ul-id}/getSetupStatus', GetULSetupStatus::class);

/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/settings/ul/{ul-id}/getAllSettings",
 *     tags={"RCQSettings"},
 *     summary="Get All the UL settings",
 *     description="Get All the UL settings",
 *    @OA\Parameter(
 *         name="role-id",
 *         in="path",
 *         description="Current User Role",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="ul-id",
 *         in="path",
 *         description="User's Unite Locale ID",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="object",
 *          ref="#/components/schemas/GetAllULSettingsResponse",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/settings/ul/{ul-id}/getAllSettings', GetAllULSettings::class);

/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/settings/ul/{ul-id}/updateRedQuestSettings",
 *     tags={"RCQSettings"},
 *     summary="Update RedQuest Settings",
 *     description="Update RedQuest Settings",
 *    @OA\Parameter(
 *         name="role-id",
 *         in="path",
 *         description="Current User Role",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="ul-id",
 *         in="path",
 *         description="User's Unite Locale ID",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->put('/{role-id:[4-9]}/settings/ul/{ul-id}/updateRedQuestSettings',  UpdateRedQuestSettings::class);



/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/settings/ul/{ul-id}/updateRedCrossQuestSettings",
 *     tags={"RCQSettings"},
 *     summary="Update RedCrossQuest Settings",
 *     description="Update RedCrossQuest Settings",
 *    @OA\Parameter(
 *         name="role-id",
 *         in="path",
 *         description="Current User Role",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="ul-id",
 *         in="path",
 *         description="User's Unite Locale ID",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
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
$app->put('/{role-id:[4-9]}/settings/ul/{ul-id}/updateRedCrossQuestSettings',  UpdateRedCrossQuestSettings::class);
