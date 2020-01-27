<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:35
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\moneyBag\GetCoinsMoneyBagDetails;
use RedCrossQuest\routes\routesActions\moneyBag\SearchMoneyBagId;



/**
 * @OA\Tag(
 *   name="MoneyBag",
 *   description="Methods related to Bank Bags that holds coins or bills."
 * )
 */



/**
 * GetMoneyBag Details
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/moneyBag",
 *     tags={"MoneyBag"},
 *     summary="Search for existing money bag IDs",
 *     description="Search money bag IDs by type (coins, bills) and a query string",
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
 *         description="The searched string",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="Type of Moneybag Id search :  'bill' or 'coin'",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="array",
 *          @OA\Items(type="string"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/moneyBag'              , SearchMoneyBagId::class);


/**
 * GetMoneyBag Details
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/moneyBag/coins/{id}",
 *     tags={"MoneyBag"},
 *     summary="Get Bank Bag details ",
 *     description="Get Bank Bag details (current amount and weight, coins count)",
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
 *         description="The ID of the Bank Bag",
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
 *          ref="#/components/schemas/CoinsMoneyBagSummaryEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/moneyBag/coins/{id}'               , GetCoinsMoneyBagDetails::class);

/**
 * GetMoneyBag Details
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/moneyBag/bills/{id}",
 *     tags={"MoneyBag"},
 *     summary="Get Bank Bag details ",
 *     description="Get Bank Bag details (current amount and weight, coins count)",
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
 *         description="The ID of the Bank Bag",
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
 *          ref="#/components/schemas/BillsMoneyBagSummaryEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/moneyBag/bills/{id}'               , GetCoinsMoneyBagDetails::class);
