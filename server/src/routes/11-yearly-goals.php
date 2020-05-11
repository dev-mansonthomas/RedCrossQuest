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

/********************************* YearlyGoals ****************************************/


/**
 * @OA\Tag(
 *   name="YearlyGoals",
 *   description="Each year, each UnitéLocale must define a budget in which the fundraising objective is set. The data is used in Spotfire graphs."
 * )
 */


/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals",
 *     tags={"YearlyGoals"},
 *     summary="Get the list of YearlyGoals",
 *     description="Get the list of YearlyGoals of an UL for a specific year. ",
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
 *    @OA\Parameter(
 *         name="year",
 *         in="query",
 *         description="The year which we want to get",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *         type="array",
 *           @OA\Items(ref="#/components/schemas/YearlyGoalEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals', ListYearlyGoals::class);

/**
 *
 * Update goal and it's split across days for an UL
 *
 */

/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals/{id}",
 *     tags={"YearlyGoals"},
 *     summary="Update the yearly goals",
 *     description="Update FundRaising goals in € and the percentage split over each days",
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
 *         name="id",
 *         in="path",
 *         description="The ID of the yearly goal (row primary key, not the year)",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/YearlyGoalEntity")
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
$app->put('/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals/{id}', UpdateYearlyGoals::class);


/**
 * Create goals for a year
 */

/**
 *
 * @OA\Post(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals",
 *     tags={"YearlyGoals"},
 *     summary="Create goals for a year",
 *     description="Update FundRaising goals in € and the percentage split over each days",
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
 *         description="Only the year is required, other fields are ignored",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\JsonContent(
 *              type="object",
 *              @OA\Schema(ref="#/components/schemas/YearlyGoalEntity")
 *            ),
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
$app->post('/{role-id:[4-9]}/ul/{ul-id}/yearlyGoals', CreateYearlyGoals::class);
