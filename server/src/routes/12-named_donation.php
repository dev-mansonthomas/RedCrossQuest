<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use RedCrossQuest\routes\routesActions\namedDonations\CreateNamedDonation;
use RedCrossQuest\routes\routesActions\namedDonations\GetNamedDonation;
use RedCrossQuest\routes\routesActions\namedDonations\ListNamedDonations;
use RedCrossQuest\routes\routesActions\namedDonations\UpdateNamedDonation;


/**
 * @OA\Tag(
 *   name="NamedDonation",
 *   description="When a person give some money and ask for a fiscal receipt, it's stored here. It's usually larger amount."
 * )
 */


/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/namedDonations",
 *     tags={"NamedDonation"},
 *     summary="Get the list of NamedDonation of an UL (the full list)",
 *     description="Get the full list of NamedDonation of an UL. Used by the frontend to put it in cache and a dropdown with local autocomplete",
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
 *          type="object",
 *           @OA\Items(ref="#/components/schemas/PageableResponseEntity"),
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
$app->get('/{role-id:[4-9]}/ul/{ul-id}/namedDonations', ListNamedDonations::class);

/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}",
 *     tags={"NamedDonation"},
 *     summary="Get one specific NamedDonation",
 *     description="Get one specific NamedDonation",
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
 *         description="The ID of the Name Parameter",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="object",
 *          ref="#/components/schemas/NamedDonationEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}', GetNamedDonation::class);

/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}",
 *     tags={"NamedDonation"},
 *     summary="Update one specific NamedDonation",
 *     description="Update one specific NamedDonation",
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
 *         description="The ID of the Name Parameter",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/NamedDonationEntity")
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
$app->put('/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}', UpdateNamedDonation::class);


/**
 *
 * @OA\Post(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/namedDonations",
 *     tags={"NamedDonation"},
 *     summary="Create one specific NamedDonation",
 *     description="Create one specific NamedDonation",
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
 *             @OA\Schema(ref="#/components/schemas/NamedDonationEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="object",
 *          ref="#/components/schemas/NamedDonationEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/namedDonations', CreateNamedDonation::class);
