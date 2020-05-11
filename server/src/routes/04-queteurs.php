<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\queteurs\AnonymizeQueteur;
use RedCrossQuest\routes\routesActions\queteurs\ApproveQueteurRegistration;
use RedCrossQuest\routes\routesActions\queteurs\AssociateRegistrationWithExistingQueteur;
use RedCrossQuest\routes\routesActions\queteurs\CountPendingQueteurRegistration;
use RedCrossQuest\routes\routesActions\queteurs\CreateQueteur;
use RedCrossQuest\routes\routesActions\queteurs\GetQueteur;
use RedCrossQuest\routes\routesActions\queteurs\GetQueteurRegistration;
use RedCrossQuest\routes\routesActions\queteurs\ListPendingQueteurRegistration;
use RedCrossQuest\routes\routesActions\queteurs\ListQueteurs;
use RedCrossQuest\routes\routesActions\queteurs\MarkAllQueteurQRCodeAsPrinted;
use RedCrossQuest\routes\routesActions\queteurs\SearchSimilarQueteurs;
use RedCrossQuest\routes\routesActions\queteurs\UpdateQueteur;

/********************************* QUETEUR ****************************************/

/**
 * @OA\Tag(
 *   name="Quêteurs",
 *   description="Queteur is the person who goes asking money on the streets."
 * )
 */

/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/queteurs",
 *     tags={"Quêteurs"},
 *     summary="Search the Queteur of an UL",
 *     description="Multiple type of search are available. If anonymization_token is present, only search for queteur matching this token. Some queries are fundRaising Related and return extra info, like where should the queteur go, how much time he is out. There's a simple search used for autocomplete search.",
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
 *         description="The string that is searched. It's matched against NIVOL, first name, last name, and id (queteur primary key)",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="searchType",
 *         in="query",
 *         description="The type of query that is being made : {0: 'search all queteur', 1: 'Search Queteur that have a TroncQueteur Prepared but have not yet left (preparation is not null and depart is null)', 2:'Search Queteur currently on the street asking for money (retour is null)', 3:'Simple search for autocomplete search}",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="secteur",
 *         in="query",
 *         description="Search a specific type of queteur : {id:1,label:'Action Sociale'},{id:2,label:'Secours'},{id:3,label:'Bénévole d\'un Jour'},{id:4,label:'Ancien Bénévole, Inactif ou Adhérent'},{id:5,label:'Commerçant'},{id:6,label:'Spécial'}",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="admin_ul_id",
 *         in="query",
 *         description="Search a specific ULID. Available only for super admin",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="active",
 *         in="query",
 *         description="Search only for active/inactive queteurs",
 *         required=false,
 *         @OA\Schema(
 *             type="boolean",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="rcqUser",
 *         in="query",
 *         description="Search only for Queteur that are also RedCrossQuest users",
 *         required=false,
 *         @OA\Schema(
 *             type="boolean",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="rcqUserActif",
 *         in="query",
 *         description="if rcqUser=true, search for RCQUser depending on their active status",
 *         required=false,
 *         @OA\Schema(
 *             type="boolean",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="benevoleOnly",
 *         in="query",
 *         description="Search for queteur that are registered volunteers",
 *         required=false,
 *         @OA\Schema(
 *             type="boolean",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="queteurIds",
 *         in="query",
 *         description="Search of queteur matching this ID list",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="QRSearchType",
 *         in="query",
 *         description="Search of queteur where the QRCode has been printed or not",
 *         required=false,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="anonymization_token",
 *         in="query",
 *         description="If this argument is present other search field are ignored. Only queteur rows that match this token will be returned",
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
 *           @OA\Items(ref="#/components/schemas/QueteurEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs'                                , ListQueteurs::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/queteurs/countPendingQueteurRegistration",
 *     tags={"Quêteurs"},
 *     summary="Count pending registration (via RedQuest)",
 *     description="Count pending registration (via RedQuest)",
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
 *          ref="#/components/schemas/CountPendingRegistrationResponse",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs/countPendingQueteurRegistration', CountPendingQueteurRegistration::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/queteurs/listPendingQueteurRegistration",
 *     tags={"Quêteurs"},
 *     summary="List pending registration",
 *     description="List pending registration (where validation has not been made) or list the one that have been accepted or refused.",
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
 *         name="registration_status",
 *         in="path",
 *         description="If registration_status is null or 0, list all registration that has not validation (granted/refused), registration_status=1 : list approved registration, registration_status:2 list refused registration",
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
 *           @OA\Items(ref="#/components/schemas/QueteurEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs/listPendingQueteurRegistration' , ListPendingQueteurRegistration::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/queteurs/searchSimilarQueteurs",
 *     tags={"Quêteurs"},
 *     summary="Search Similar Queteur",
 *     description="Search Queteur that have a first name, last name, nivol that are like what has been typed. At least one parameter must be given. If mutliple argument is given, the search is done with an 'OR'",
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
 *         name="first_name",
 *         in="query",
 *         description="Search for matching first name",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="last_name",
 *         in="query",
 *         description="Search for matching last name",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="first_name",
 *         in="query",
 *         description="Search for matching nivol",
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
 *           @OA\Items(ref="#/components/schemas/QueteurEntity"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs/searchSimilarQueteurs'          , SearchSimilarQueteurs::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}",
 *     tags={"Quêteurs"},
 *     summary="Get a Queteur by its ID",
 *     description="The queteur must belong to the RCQ user's UL (except for super admin). If the current RCQ User is admin, the queteur's user details is also fetched.",
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
 *         description="ID of the queteur",
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
 *          ref="#/components/schemas/QueteurEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}'                           , GetQueteur::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}/getQueteurRegistration",
 *     tags={"Quêteurs"},
 *     summary="Get a Queteur Registration by its registration ID",
 *     description="Get a Queteur Registration by its ID, and if the registration_approved is null and from the same UL as the connected user.",
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
 *         description="ID of the queteur registration",
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
 *          ref="#/components/schemas/QueteurEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}/getQueteurRegistration'    , GetQueteurRegistration::class);

/**
 *
 * @OA\Put(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/queteurs/markAllAsPrinted",
 *     tags={"Quêteurs"},
 *     summary="Mark all QRCode as printed",
 *     description="Mark all QRCode as printed for the user's UL",
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
 *         description="Success"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->put('/{role-id:[2-9]}/ul/{ul-id}/queteurs/markAllAsPrinted'                             , MarkAllQueteurQRCodeAsPrinted::class);
/**
 *
 * @OA\Put(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}/getQueteurRegistration",
 *     tags={"Quêteurs"},
 *     summary="Get a Queteur Registration by its registration ID",
 *     description="Get a Queteur Registration by its ID, and if the registration_approved is null and from the same UL as the connected user.",
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
 *         description="ID of the queteur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/QueteurEntity")
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
$app->put('/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}'                                         , UpdateQueteur::class);
/**
 *
 * @OA\Put(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}/anonymize",
 *     tags={"Quêteurs"},
 *     summary="Anonymize a queteur and send the queteur an email",
 *     description="This method retrieve the current queteur data, anonymize the queteur data in DB and set a GUID token on that row. Then the queteur is sent an email with the token. If the queteur return the next year with that token, his queteur row can be re-valueated with nomminative data. His previous fundraising details can be retrieved and used in gamification. The anonymized data is returned for display",
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
 *         description="ID of the queteur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/QueteurEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         ref="#/components/schemas/QueteurEntity"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->put('/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}/anonymize'                               , AnonymizeQueteur::class);
/**
 *
 * @OA\Put(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}/associateRegistrationWithExistingQueteur",
 *     tags={"Quêteurs"},
 *     summary="Associate an existing queteur with a queteur registration",
 *     description="If an existing queteur wants to use RedQuest, he must register to RedQuest, and this registration must be approved and do not generate a duplicate. When the registration is opened, a searchSimilarQueteur is performed, and similar queteur are displayed. The user have a button to associate the registration to the existing RCQ Queteur. This button call this function, and pass the queteurEntity. But before that : the queteur registration id, must be copied in queteur->registration_id, and the  selected QueteurId must be set in queteur->id. ul_registration_token is validated. A confirmation email is sent, and a pub_sub message is sent to RedQuest.",
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
 *         description="ID of the queteur (the existing one in RCQ)",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="The queteur Entity that contains the registration_id (the primary key of queteur_registration table)",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/QueteurEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         ref="#/components/schemas/AssociateRegistrationWithExistingQueteurResponse"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->put('/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}/associateRegistrationWithExistingQueteur', AssociateRegistrationWithExistingQueteur::class);

/**
 *
 * @OA\Post(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/queteurs",
 *     tags={"Quêteurs"},
 *     summary="Create a new Queteur",
 *     description="Create a queteur in current queteur's UL. Super admin can create queteur on other Uls (for first RCQ User or debug)",
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
 *         description="The queteur Entity that contains queteur's info except id. User is created later with another method",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/QueteurEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         ref="#/components/schemas/CreateQueteurResponse"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->post('/{role-id:[2-9]}/ul/{ul-id}/queteurs'                                             , CreateQueteur::class);
/**
 *
 * @OA\Post(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/queteurs/approveQueteurRegistration",
 *     tags={"Quêteurs"},
 *     summary="Accept or refuse a queteur registration.",
 *     description="ul_registration_token is validated. Create a new Queteur from a Registration and send an email if the registration is approved, or send an email to inform of the refusal. QueteurRegistration is updated and linked to the queteur if approved. A message is also sent to RedQuest via pubSub",
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
 *         description="The queteur Entity that contains queteur's info except id. User is created later with another method",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/QueteurEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         ref="#/components/schemas/ApproveQueteurRegistrationResponse"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->post('/{role-id:[2-9]}/ul/{ul-id}/queteurs/approveQueteurRegistration'                  , ApproveQueteurRegistration::class);



