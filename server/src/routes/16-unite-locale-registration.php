<?php

/********************************* Application Settings Exposed to GUI ****************************************/

use RedCrossQuest\routes\routesActions\ulRegistration\CreateUniteLocaleInLowerEnv;
use RedCrossQuest\routes\routesActions\ulRegistration\RegisterNewUL;
use RedCrossQuest\routes\routesActions\ulRegistration\SearchUnregisteredUniteLocale;
use RedCrossQuest\routes\routesActions\ulRegistration\ValidateULRegistration;

/**
 * @OA\Tag(
 *   name="UnitéLocaleRegistration",
 *   description="API to manage the UL Registration"
 * )
 */

/**
 *
 * @OA\Get(
 *     path="/ul_registration/",
 *     tags={"UnitéLocaleRegistration"},
 *     summary="Search UL by Name/PostalCode/City",
 *     description="Search UL by Name/PostalCode/City",
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
$app->get('/ul_registration', SearchUnregisteredUniteLocale::class);

/**
 *
 * @OA\Post(
 *     path="/ul_registration",
 *     tags={"UnitéLocaleRegistration"},
 *     summary="Register a new UL",
 *     description="Register a new UL",
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
$app->post('/ul_registration', RegisterNewUL::class);

/**
 *
 * @OA\Post(
 *     path="/ul_registration/check_registration_code",
 *     tags={"UnitéLocaleRegistration"},
 *     summary="Check Registration Code to Register a new UL",
 *     description="Check Registration Code to Register a new UL",
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
$app->post('/ul_registration/check_registration_code', ValidateULRegistration::class);

/**
 * TODO :  update method signature
 * 
 * @OA\Post(
 *     path="/ul_registration/create_ul_in_lower_env",
 *     tags={"UnitéLocaleRegistration"},
 *     summary="Create the new UL that has been validated in production in test environmnent",
 *     description="Create the new UL that has been validated in production in test environmnent",
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
$app->post('/ul_registration/create_ul_in_lower_env', CreateUniteLocaleInLowerEnv::class);



