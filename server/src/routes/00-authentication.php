<?php /** @noinspection SpellCheckingInspection */

/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/4017
 * Time: 18:36
 */
use RedCrossQuest\routes\routesActions\authentication\AuthenticateAction;
use RedCrossQuest\routes\routesActions\authentication\FirebaseAuthenticateAction;
use RedCrossQuest\routes\routesActions\authentication\GetUserInfoFromUUIDAction;
use RedCrossQuest\routes\routesActions\authentication\ResetPassword;
use RedCrossQuest\routes\routesActions\authentication\SendPasswordInitializationMailAction;


/********************************* Authentication ****************************************/

/**
 * @OA\Tag(
 *   name="Authentication",
 *   description="Authentication related API."
 * )
 */



/**
 * Authenticate from Firebase with a JWT token from firebase.
 * This token is then checked and on success, the method will retrieve the info about the user and generate a RCQ JWT

 * @OA\Post(
 *     path="/firebase-authenticate",
 *     tags={"Authentication"},
 *     summary="Returns a JWT Token for RedCrossQuest API if the firebase JWT is validated",
 *     description="In the context of a firebase application, this method validate the firebase JWT and genenrate an RCQ JWT",
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="email",
 *                     description="email of the person being authenticated",
 *                     type="string",
 *                 ),
 *                 @OA\Property(
 *                     property="token",
 *                     description="firebase JWT",
 *                     type="string"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(ref="#/components/schemas/AuthenticationResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Authorisation denied"
 *     )
 * )
 */

/** @noinspection PhpUndefinedVariableInspection */
$app->post('/firebase-authenticate', FirebaseAuthenticateAction::class);

/**
 * Authenticate from AngularJS old front-end with username/password against internal DB
 * and generate a RCQ JWT
 *
 * @OA\Post(
 *     path="/authenticate",
 *     tags={"Authentication"},
 *     summary="Authenticate a RCQ user",
 *     description="Returns a JWT Token for RedCrossQuest API if the credentials are validated & the reCapcha  is validated",
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="username",
 *                     description="user's nivol",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     description="user's password",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="token",
 *                     description="ReCapcha Token to be validated",
 *                     type="string"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(ref="#/components/schemas/AuthenticationResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Authorisation denied"
 *     )
 * )
 */
$app->post('/authenticate'         , AuthenticateAction::class);

/**
 * for the user with the specified nivol, update the 'init_passwd_uuid' and 'init_passwd_date' field in DB
 * and send the user an email with a link, to reach the reset password form
 *
 * @OA\Post(
 *     path="/sendInit",
 *     tags={"Authentication"},
 *     summary="user's password reinitialization",
 *     description="Send an email to the user with a link to reinitialize his password",
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="username",
 *                     description="user's nivol",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="token",
 *                     description="ReCapcha Token to be validated",
 *                     type="string"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(ref="#/components/schemas/SendPasswordInitializationMailResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Authorisation denied - recaptcha validation fails"
 *     )
 * )
 */
$app->post('/sendInit', SendPasswordInitializationMailAction::class);

/**
 * Get user information from the UUID
 * Used in the reinit password process
 *
 * @OA\Get(
 *     path="/getInfoFromUUID",
 *     tags={"Authentication"},
 *     summary="fetch user info from UUID for password reinitialisation",
 *     description="When a user click on the link in the email to reinitialize its email, there's a UUID passed in the URL. From it, the process of reinitialization is validated and the username of the user is fetch",
 *    @OA\Parameter(
 *         name="uuid",
 *         in="query",
 *         description="The UUID",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="token",
 *         in="query",
 *         description="recaptcha token",
 *         required=true,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(ref="#/components/schemas/GetUserInfoFromUUIDResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Authorisation denied - recaptcha validation fails"
 *     )
 * )
 */
$app->get('/getInfoFromUUID', GetUserInfoFromUUIDAction::class);

/**
 * save new password of user
 *
 * @OA\Post(
 *     path="/resetPassword",
 *     tags={"Authentication"},
 *     summary="save the new password of the user",
 *     description="Check ReCaptcha and save the new password for the user",
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="uuid",
 *                     description="user's UUID for password reset request (recieved by email)",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="password",
 *                     description="the new password",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="token",
 *                     description="ReCapcha Token to be validated",
 *                     type="string"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(ref="#/components/schemas/ResetPasswordResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Authorisation denied - recaptcha validation fails"
 *     )
 * )
 */
$app->post('/resetPassword', ResetPassword::class);
