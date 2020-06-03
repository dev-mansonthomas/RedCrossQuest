<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use RedCrossQuest\routes\routesActions\exportData\ExportData;

/**
 * @OA\Tag(
 *   name="ExportData",
 *   description="The UL can export all its data in CSV format."
 * )
 */

/**
 * Export Data of the Unite Local to a file and download it
 *
 * Dispo pour le role admin local
 */
/**
 *
 * @OA\Get(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/exportData",
 *     tags={"ExportData"},
 *     summary="Export UL's data",
 *     description="Export UL's data as several CSV files, packed in a zip file protected by password",
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
 *          type="object",
 *          ref="#/components/schemas/ExportDataResponse",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/exportData', ExportData::class);



