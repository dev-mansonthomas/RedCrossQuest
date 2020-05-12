<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use RedCrossQuest\routes\routesActions\users\CreateUser;
use RedCrossQuest\routes\routesActions\users\ReInitUserPassword;
use RedCrossQuest\routes\routesActions\users\UpdateUser;

/********************************* USERS ****************************************/


/**
 * @OA\Tag(
 *   name="Users",
 *   description="Users of RCQ. The 'Get' is automatically done if the queteur is also a user of RCQ"
 * )
 */


/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/users/{id}",
 *     tags={"Users"},
 *     summary="Update the active/inactive status and role of the user",
 *     description="Before reactivating a user that was previously disabled, a check is done to see if an active user already exists with that nivol, whatever the UL. If it's the case, and error is raised.",
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
 *         description="Id of the user",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/UserEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         ref="#/components/schemas/UserEntity",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->put ('/{role-id:[4-9]}/ul/{ul-id}/users/{id}'               , UpdateUser::class);
/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/users/{id}/reInitPassword",
 *     tags={"Users"},
 *     summary="Initiate the reset password procedure",
 *     description="Generate a GUID with an expiry date of 48h for the first init, 4h for subsequent init and send an email to the user (it's email is taken from the corresponding queteur object) and return the updated UserEntityObject",
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
 *         description="Id of the user",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/UserEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         ref="#/components/schemas/UserEntity",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->put ('/{role-id:[4-9]}/ul/{ul-id}/users/{id}/reInitPassword', ReInitUserPassword::class);
/**
 *
 * @OA\Put(
 *     path="/{role-id:[4-9]}/ul/{ul-id}/users",
 *     tags={"Users"},
 *     summary="Create a user",
 *     description="Create a new User. Check that NIVOL or UL_ID is not changed. Check if an active user with that NIVOL exists in any UL, if so it raises an error. ",
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
 *         description="Id of the user",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/UserEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         ref="#/components/schemas/UserEntity",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/users'                    , CreateUser::class);
