<?php

/********************************* Application Settings Exposed to GUI ****************************************/

use RedCrossQuest\routes\routesActions\settings\UpdateULSettings;
use RedCrossQuest\routes\routesActions\unitesLocales\GetUniteLocale;
use RedCrossQuest\routes\routesActions\unitesLocales\ListUniteLocale;

/**
 * @OA\Tag(
 *   name="UnitéLocale",
 *   description="UniteLocale is the smallest structure in the RedCross. There's usually one Unit per city or District of big city."
 * )
 */

/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/ul/{id}",
 *     tags={"UnitéLocale"},
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
 *         name="id",
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
 *          ref="#/components/schemas/UniteLocaleEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[4-9]}/ul/{id}', GetUniteLocale::class);

/**
 *
 * @OA\Get(
 *     path="/{role-id:[9]}/ul",
 *     tags={"UnitéLocale"},
 *     summary="Search for UL",
 *     description="Search for UL against those fields : against `name`, `postal_code`, `city`, `president_first_name`, `president_last_name`, `admin_first_name`, `admin_last_name`, `tresorier_first_name`, `tresorier_last_name`. only for super admin.",
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
 *    @OA\Parameter(
 *         name="q",
 *         in="query",
 *         description="String to search ",
 *         required=true,
 *         @OA\Schema(
 *             type="String",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="array",
 *           @OA\Items(ref="#/components/schemas/UniteLocaleEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[9]}/ul', ListUniteLocale::class);






/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/ul/{ul-id}",
 *     tags={"UnitéLocale"},
 *     summary="Update Unité Locale Settings",
 *     description="Update Unité Locale Settings",
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
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/UniteLocaleEntity")
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
$app->put('/{role-id:[4-9]}/ul/{ul-id}', UpdateULSettings::class);




