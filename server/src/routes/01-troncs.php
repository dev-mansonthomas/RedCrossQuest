<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:33
 */

use RedCrossQuest\routes\routesActions\troncs\GetTronc;
use RedCrossQuest\routes\routesActions\troncs\InsertTronc;
use RedCrossQuest\routes\routesActions\troncs\ListTroncs;
use RedCrossQuest\routes\routesActions\troncs\UpdateTronc;

/********************************* TRONC ****************************************/

/**
 * @OA\Tag(
 *   name="Troncs",
 *   description="Methods that apply to a Tronc. Tronc is a french word for a sealed metal box used to collect money (collection box). (it has also other meaning)."
 * )
 */

/**
 * Fetch the list of tronc with search criteria
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/troncs",
 *     tags={"Troncs"},
 *     summary="Fetch the list of tronc with search criteria",
 *     description="List all tronc by default. If filters are specified, the list can be filter by type, active state, and tronc id",
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
 *         name="active",
 *         in="query",
 *         description="filter tronc by active criteria (boolean)",
 *         required=false,
 *         @OA\Schema(
 *             type="boolean",
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="filter by type of tronc : {id:1,label:'Tronc'},{id:2,label:'Tronc chez un commerçant'},{id:3,label:'Autre'}",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="q",
 *         in="query",
 *         description="Filter by tronc id. Only one tronc id value is supported. The query is implemented this way : AND CONVERT(id, CHAR) like concat(:query,'%')",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/TroncEntity")
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/troncs', ListTroncs::class);

/**
 * récupère le détails d'un tronc (a enrichir avec les troncs_queteurs associés)
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/troncs/{id}",
 *     tags={"Troncs"},
 *     summary="Get a specific Tronc",
 *     description="When a user click on the link in the email to reinitialize its email, there's a UUID passed in the URL. From it, the process of reinitialization is validated and the username of the user is fetch",
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
 *         description="the id of the tronc you're trying to get",
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
 *          ref="#/components/schemas/TroncEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/troncs/{id}', GetTronc::class);

/**
 * Update le tronc, seulement pour l'admin
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/troncs/{id}",
 *     tags={"Troncs"},
 *     summary="Update a tronc, only for admin role (4)",
 *     description="Update type, notes, enabled status",
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
 *         description="the id of the tronc you're trying to update",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/TroncEntity")
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
$app->put('/{role-id:[4-9]}/ul/{ul-id}/troncs/{id}', UpdateTronc::class);

/**
 * Insert le tronc, seulement pour l'admin
 *
 * @OA\Post(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/troncs",
 *     tags={"Troncs"},
 *     summary="Insert new TroncS",
 *     description="Only for admin role (4). Insert X troncs in the DB, where X=nombreTronc in TroncEntity",
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
 *         description="the id of the tronc you're trying to update",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/TroncEntity")
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
$app->post('/{role-id:[4-9]}/ul/{ul-id}/troncs', InsertTronc::class);


