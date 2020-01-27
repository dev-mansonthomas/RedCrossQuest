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
 * @OA\Tag(
 *   name="ThanksMailing",
 *   description="Once the fundraising is done, a Thank You mailing is sent. This methods starts the mailing and monitor its progress. A method tracks if the graph has been seen or not"
 * )
 */

/**
 * Get summary info about mailing
 *
 * Dispo pour le role admin local
 */

/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/mailing",
 *     tags={"ThanksMailing"},
 *     summary="Get the summary of this year ThanksMailing",
 *     description="Get the summary of this year ThanksMailing",
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
 *         description="Searched String. Matched against these fields : `first_name`, `last_name`, `email`, `phone`, `ref_recu_fiscal`",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="deleted",
 *         in="query",
 *         description="Search for deleted named donation",
 *         required=true,
 *         @OA\Schema(
 *             type="boolean",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="year",
 *         in="query",
 *         description="Search NamedDonation of a specific year",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="admin_ul_id",
 *         in="query",
 *         description="For super admin, search named donation for other UL",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="array",
 *           @OA\Items(ref="#/components/schemas/NamedDonationEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
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



