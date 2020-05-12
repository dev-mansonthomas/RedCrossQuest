<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:35
 */

use RedCrossQuest\routes\routesActions\troncQueteurHistory\GetTroncQueteurHistoryFromTQID;


/********************************* TRONC_QUETEUR_HISTORY (use TroncsQueteurs TAG) ****************************************/


/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur_history",
 *     tags={"TroncsQueteurs"},
 *     summary="Get the history of modification of a TroncQueteur",
 *     description="Get the history of modification of a TroncQueteur. it's ordered by id desc",
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
 *         name="tronc_queteur_id",
 *         in="query",
 *         description="The ID of the TroncQueteur",
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
 *           @OA\Items(ref="#/components/schemas/TroncQueteurEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur_history', GetTroncQueteurHistoryFromTQID::class);




