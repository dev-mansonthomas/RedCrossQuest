<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use RedCrossQuest\routes\routesActions\spotfire\GetSpotfireAccessToken;

/**
 * @OA\Tag(
 *   name="SpotfireAccess",
 *   description="TIBCO Spotfire is a Business Intelligence cloud solution used by RCQ to analyse the data."
 * )
 */


/**
 *
 * @OA\Get(
 *     path="/{role-id:[1-9]}/ul/{ul-id}/graph",
 *     tags={"SpotfireAccess"},
 *     summary="Get the users its spotfire token",
 *     description="Check if a valid spotfire token exist. If so it returns it. if not it creates a new one. The Spotfire Access Token is a GUID that allow to display the appropriate data in spotfire graphs",
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
 *          ref="#/components/schemas/SpotfireAccessEntity",
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
$app->get('/{role-id:[1-9]}/ul/{ul-id}/graph', GetSpotfireAccessToken::class);


/*
 Documentation :



Au click sur un graph Spotfire, l’application insert une ligne dans la table spotfire_access.
Avec un token, une date d’expiration, l’UL_ID répliqué eviter des jointures inutiles, et le user_id (jointure vers table user pour checker s’il est actif et récupérer son role)

$spotfire_access_table = $this->table('spotfire_access');
$spotfire_access_table
  ->addColumn('token'           , 'string', array('limit' => 36))
  ->addColumn('token_expiration', 'datetime')
  ->addColumn('ul_id'           , 'integer')
  ->addColumn('user_id'         , 'integer')

  ->addForeignKey('ul_id'  , 'ul'   , 'id')
  ->addForeignKey('user_id', 'users', 'id')
  ->create();



Spotfire est mise à jour toutes les 2 minutes, minutes paires.
L’application RCQ attends que la prochaine minutes paires soit passée, puis ouvre le graph spotfire avec le token en parametre.
Le calcul de la colonne show=true se fait avec les critères suivant:
* Token trouvé dans la table spotfire_access
* La date courante est avant la date d’expiration
* l’utilisateur est enabled
* le role_id est supérieur parametre du dashboard.


 * */
