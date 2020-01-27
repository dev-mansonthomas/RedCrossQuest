<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:36
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\pointsQuetes\CreatePointQuete;
use RedCrossQuest\routes\routesActions\pointsQuetes\GetPointQuete;
use RedCrossQuest\routes\routesActions\pointsQuetes\ListPointsQuetes;
use RedCrossQuest\routes\routesActions\pointsQuetes\SearchPointsQuetes;
use RedCrossQuest\routes\routesActions\pointsQuetes\UpdatePointQuete;

/********************************* POINT_QUETE ****************************************/
/**
 * @OA\Tag(
 *   name="PointQuete",
 *   description="PointQuete is the location where people go ask money on the street."
 * )
 */


/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/pointQuetes/{id:\d+}",
 *     tags={"PointQuete"},
 *     summary="Get a specific PointDeQuete",
 *     description="Get a specific PointDeQuete (Geographical location where Queteur ask for money)",
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
 *         description="The ID of the PointDeQuete",
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
 *          ref="#/components/schemas/PointQueteEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/pointQuetes/{id:\d+}', GetPointQuete::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/pointQuetes",
 *     tags={"PointQuete"},
 *     summary="Get the list of PointDeQuete of an UL (the full list)",
 *     description="Get the full list of PointDeQuete of an UL. Used by the frontend to put it in cache and a dropdown with local autocomplete",
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
 *          type="array",
 *           @OA\Items(ref="#/components/schemas/PointQueteEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/pointQuetes', ListPointsQuetes::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/pointQuetes/search",
 *     tags={"PointQuete"},
 *     summary="Search PointDeQuete of an UL",
 *     description="Search Point de Quete. The Query string (q) is match against name, code, adress, city. Filter by type & active state. Search in the current UL, except for the super admin that can override the ULID",
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
 *         description="The string that is searched",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="point_quete_type",
 *         in="query",
 *         description="The type of point de quete {id:1,label:'Voie Publique / Feux Rouge'},{id:2,label:'Piéton'},{id:3,label:'Commerçant'},{id:4,label:'Base UL'},{id:5,label:'Autre'}",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="active",
 *         in="query",
 *         description="Search of active/inactive pointDeQuete",
 *         required=true,
 *         @OA\Schema(
 *             type="boolean",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="admin_ul_id",
 *         in="query",
 *         description="Search a specific ULID. Available only for super admin",
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
 *           @OA\Items(ref="#/components/schemas/PointQueteEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/pointQuetes/search', SearchPointsQuetes::class);




/**
 * @OA\Post(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/pointQuetes/{id}",
 *     tags={"PointQuete"},
 *     summary="Record a preparation or preparation and departure",
 *     description="Record the fact that queteur queteur_id=X will use the Tronc tronc_id=Y at a specific time (preparation date) and place (PointQueteId). The departure can be recorded with this operation as well if Troncqueteur->preparationAndDepart is set to true",
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
 *         description="Id of the Point de Quete we want to update",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/PointQueteEntity")
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
$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/pointQuetes/{id}', UpdatePointQuete::class);

/**
 * @OA\Post(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/pointQuetes",
 *     tags={"PointQuete"},
 *     summary="Create a pointDeQuete",
 *     description="Create a pointDeQuete :  ie a specific location where queteur go asking for money",
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
 *             @OA\Schema(ref="#/components/schemas/PointQueteEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         ref="#/components/schemas/CreatePointQueteResponse"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/pointQuetes', CreatePointQuete::class);
