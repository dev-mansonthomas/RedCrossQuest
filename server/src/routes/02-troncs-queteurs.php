<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:35
 */

use RedCrossQuest\routes\routesActions\troncsQueteurs\CancelDepartOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\CancelRetourOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\DeleteNonReturnedTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetAndSetDepartOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetLastTroncQueteurFromTroncId;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetTroncsQueteursForTroncId;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetTroncsQueteursOfQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\PrepareTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\SaveAsAdminOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\SaveCoinsOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\SaveReturnDateOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\TroncQueteurPreparationChecks;

/********************************* TRONC_QUETEUR ****************************************/

/**
 * @OA\Tag(
 *   name="TroncsQueteurs",
 *   description="Methods that apply to the TroncQueteur object. Which hold the information of Who(Queteur) goes where, at what time and with What(Tronc)."
 * )
 */


/**
 * @OA\Get(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/preparationChecks",
 *     tags={"TroncsQueteurs"},
 *     summary="Checks if the tronc or the queteur are not already in use",
 *     description="Checks if the tronc or the queteur are not already in use. This check is done when saving the preparation. This method allows to check just after filling tronc & queteur and before point de quete",
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
 *         name="tronc_id",
 *         in="query",
 *         description="The ID of the Tronc that is being checked",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="queteur_id",
 *         in="query",
 *         description="The ID of the Queteur that is being checked",
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
 *          ref="#/components/schemas/PrepareTroncQueteurResponse",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/preparationChecks', TroncQueteurPreparationChecks::class);


/**
 * @OA\Delete(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/nonReturnedTroncQueteur/{tronc_id}",
 *     tags={"TroncsQueteurs"},
 *     summary="Mark as deleted unused tronc_queteur that is linked to the {tronc_id}",
 *     description="When doing a preparation, a check is done, to see if the queteur is not already linked to another tronc_id or the tronc_id to another queteur in a tronc_queteur row, and that tronc_queteur has depart or retour = null. If it's the case, a popup is shown offering to mark the existing tronc_queteur as deleted. The deletion is performed by this method, on the specified tronc_id",
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
 *         name="tronc_id",
 *         in="path",
 *         description="The ID of the Tronc, used as search criteria on TroncQueteur that have retour or depart = null, to set them as deleted",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
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
$app->delete('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/nonReturnedTroncQueteur/{tronc_id}', DeleteNonReturnedTroncQueteur::class);

/**
 * @OA\Patch(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveReturnDate",
 *     tags={"TroncsQueteurs"},
 *     summary="Update the TroncQueteur with money details",
 *     description="Update TroncQueteur with coins, bills, credit card, bank notes details, set the 'comptage' date and publish a message on PubSub",
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
 *         description="The ID of the TroncQueteur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="dateDepartIsMissing",
 *         in="query",
 *         description="When scanning a Tronc for a 'Retour' and the Depart date is missing, the user can fill the missing departure date and record the return date at the same time. If so, we add a parameter dateDepartIsMissing=true to notify backend that the depart must be updated",
 *         required=true,
 *         @OA\Schema(
 *             type="boolean",
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
$app->patch('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveReturnDate', SaveReturnDateOnTroncQueteur::class);


/**
 *
 * @OA\Patch(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveCoins",
 *     tags={"TroncsQueteurs"},
 *     summary="Update the TroncQueteur with money details",
 *     description="Update TroncQueteur with coins, bills, credit card, bank notes details, set the 'comptage' date and publish a message on PubSub",
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
 *         description="The ID of the TroncQueteur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *         )
 *     ),
 *    @OA\Parameter(
 *         name="adminMode",
 *         in="query",
 *         description="If set to true, the comptage date is not updated to now",
 *         required=true,
 *         @OA\Schema(
 *             type="boolean",
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
$app->patch('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveCoins'     , SaveCoinsOnTroncQueteur::class);

/**
 *
 * @OA\Patch(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveAsAdmin",
 *     tags={"TroncsQueteurs"},
 *     summary="Update the TroncQueteur as admin",
 *     description="Outside of the normal lifecycle, edit the TroncQueteur to correct dates, money etc...",
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
 *         description="The ID of the TroncQueteur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
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
$app->patch('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveAsAdmin'   , SaveAsAdminOnTroncQueteur::class);



/**
 *
 * @OA\Patch(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/cancelDepart",
 *     tags={"TroncsQueteurs"},
 *     summary="Cancel the 'Depart'",
 *     description="Cancel the Depart (Volunteer start collecting money) by setting the 'depart' date to null",
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
 *         description="The ID of the TroncQueteur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
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
$app->patch('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/cancelDepart'  , CancelDepartOnTroncQueteur::class);

/**
 *
 * @OA\Patch(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/cancelRetour",
 *     tags={"TroncsQueteurs"},
 *     summary="Cancel the 'Retour'",
 *     description="Cancel the Retour (Return of the volunteer) by setting the 'retour' date to null",
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
 *         description="The ID of the TroncQueteur",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
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
$app->patch('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/cancelRetour'  , CancelRetourOnTroncQueteur::class);


/**
 * Get a tronc_queteur and set departure date
 *
 * @OA\Patch(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/getTroncQueteurForTroncIdAndSetDepart",
 *     tags={"TroncsQueteurs"},
 *     summary="Record Depart (start of collecting money) for a tronc ID",
 *     description="Retrieve the correct TroncQueteur from the Tronc ID (decoded from the QRCode) and record the depart",
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
 *         name="tronc_id",
 *         in="query",
 *         description="The ID of the Tronc (scan with QRCode or hand typed)",
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
 *          ref="#/components/schemas/TroncQueteurEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->patch('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/getTroncQueteurForTroncIdAndSetDepart', GetAndSetDepartOnTroncQueteur::class);

/**
 * Prepare a tronc_queteur (insert) or Prepare & Set depart (insert + update depart)
 * If departure date is already set,  tronc_queteur is returned with departAlreadyRegistered=true
 *
 * @OA\Post(
 *     path="/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur",
 *     tags={"TroncsQueteurs"},
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
 *     @OA\RequestBody(
 *         description="Input data format",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(ref="#/components/schemas/TroncQueteurEntity")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *          type="object",
 *          ref="#/components/schemas/PrepareTroncQueteurResponse",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */

$app->post('/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur', PrepareTroncQueteur::class);



/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getLastTroncQueteurFromTroncId",
 *     tags={"TroncsQueteurs"},
 *     summary="Get the last recored TroncQueteur linked to the passed TroncID",
 *     description="Get the last recored TroncQueteur linked to the passed TroncID. Queteur and PointQuete object are fully retrieved. If not found, empty TroncQueteur is returned with numRows=0, and tronc_id = value passed",
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
 *         name="tronc_id",
 *         in="query",
 *         description="The ID of the Tronc",
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
 *          ref="#/components/schemas/TroncQueteurEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getLastTroncQueteurFromTroncId', GetLastTroncQueteurFromTroncId::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getTroncsQueteurForTroncId",
 *     tags={"TroncsQueteurs"},
 *     summary="Get all the TroncQueteur linked to a Tronc",
 *     description="Get the last recored TroncQueteur linked to the passed TroncID. Queteur and PointQuete object are fully retrieved. If not found, empty TroncQueteur is returned with numRows=0, and tronc_id = value passed",
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
 *         name="tronc_id",
 *         in="query",
 *         description="The ID of the Tronc",
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
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getTroncsQueteurForTroncId'    , GetTroncsQueteursForTroncId::class);
/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getTroncsOfQueteur",
 *     tags={"TroncsQueteurs"},
 *     summary="Get all the TroncQueteur linked to a Queteur",
 *     description="Get the last recored TroncQueteur linked to the passed QueteurID. Queteur and PointQuete object are fully retrieved. If not found, empty TroncQueteur is returned with numRows=0, and tronc_id = value passed",
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
 *         name="queteur_id",
 *         in="query",
 *         description="The ID of the Queteur",
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
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getTroncsOfQueteur'            , GetTroncsQueteursOfQueteur::class);


/**
 * récupère un tronc_queteur par son id
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/{id}",
 *     tags={"TroncsQueteurs"},
 *     summary="Get a TroncQueteur from its ID ",
 *     description="Get A TroncQueteur from its ID (TroncQueteur->id) and get the full objects for Queteur, Tronc, PointQuete",
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
 *          type="object",
 *          ref="#/components/schemas/TroncQueteurEntity",
 *         ),
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="General Error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
 *     )
 * )
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/{id:\d+}', GetTroncQueteur::class);









