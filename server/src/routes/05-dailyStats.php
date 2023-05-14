<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use RedCrossQuest\routes\routesActions\dailyStats\CreateYearOfDailyStats;
use RedCrossQuest\routes\routesActions\dailyStats\ListDailyStats;
use RedCrossQuest\routes\routesActions\dailyStats\UpdateDailyStats;


/********************************* Daily Stats ****************************************/

/**
 * @OA\Tag(
 *   name="DailyStatsBeforeRCQ",
 *   description="Methods that store/retrieve/update daily collection figures of previous years, so UL new to RCQ can compare the current year with the past"
 * )
 */

/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/dailyStats",
 *     tags={"DailyStatsBeforeRCQ"},
 *     summary="Get the daily stats of a specific year",
 *     description="Get the daily stats of a specific year. That is the amount of money collected on each day of the fundraising of that year",
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
 *         name="year",
 *         in="query",
 *         description="The year we want the daily stats",
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
 *          ref="#/components/schemas/DailyStatsBeforeRCQEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
/** @noinspection PhpUndefinedVariableInspection */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/dailyStats', ListDailyStats::class);


/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/dailyStats/{id}",
 *     tags={"DailyStatsBeforeRCQ"},
 *     summary="Get the daily stats of a specific year",
 *     description="Get the daily stats of a specific year. That is the amount of money collected on each day of the fundraising of that year",
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
 *         description="ID of the stat to update",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/DailyStatsBeforeRCQEntity")
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
$app->put('/{role-id:[4-9]}/ul/{ul-id}/dailyStats/{id}', UpdateDailyStats::class);

/**
 *
 * @OA\Post(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/dailyStats",
 *     tags={"DailyStatsBeforeRCQ"},
 *     summary="Create the rows of stats for a specific year",
 *     description="Insert a row per day of fundraising on that specific year. Amount is set to 0. Dates per year is configured on the server side configuration",
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
 *         name="year",
 *         in="query",
 *         description="Year to be created",
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
$app->post('/{role-id:[4-9]}/ul/{ul-id}/dailyStats', CreateYearOfDailyStats::class);



