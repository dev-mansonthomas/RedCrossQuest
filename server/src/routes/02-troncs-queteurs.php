<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:35
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\troncsQueteurs\CancelDepartOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\CancelRetourOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\DeleteTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetAndSetDepartOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetLastTroncQueteurFromTroncId;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetMoneyBagDetails;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetTroncsQueteursForTroncId;
use RedCrossQuest\routes\routesActions\troncsQueteurs\GetTroncsQueteursOfQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\PrepareTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\SaveAsAdminOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\SaveCoinsOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\SaveReturnDateOnTroncQueteur;
use RedCrossQuest\routes\routesActions\troncsQueteurs\SearchMoneyBagId;

/********************************* TRONC_QUETEUR ****************************************/

/**
 * Supprime les tronc_queteurs qui implique le tronc ({id}) et qui ont soit la colonne départ ou retour de null.
 */
$app->delete(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}', DeleteTroncQueteur::class);


//TODO convert to PATCH
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveReturnDate', SaveReturnDateOnTroncQueteur::class);
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveCoins'     , SaveCoinsOnTroncQueteur::class);
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/saveAsAdmin'   , SaveAsAdminOnTroncQueteur::class);
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/cancelDepart'  , CancelDepartOnTroncQueteur::class);
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/{id}/cancelRetour'  , CancelRetourOnTroncQueteur::class);

/**
 * Get a tronc_queteur and set departure date
 */
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur/getTroncQueteurForTroncIdAndSetDepart', GetAndSetDepartOnTroncQueteur::class);

/**
 * Prepare a tronc_queteur (insert) or Prepare & Set depart (insert + update depart)
 * If departure date is already set,  tronc_queteur is returned with departAlreadyRegistered=true
 */
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/tronc_queteur', PrepareTroncQueteur::class);

$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getLastTroncQueteurFromTroncId', GetLastTroncQueteurFromTroncId::class);
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getTroncsQueteurForTroncId'    , GetTroncsQueteursForTroncId::class);
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/getTroncsOfQueteur'            , GetTroncsQueteursOfQueteur::class);
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/searchMoneyBagId'              , SearchMoneyBagId::class);
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/moneyBagDetails'               , GetMoneyBagDetails::class);

/**
 * récupère un tronc_queteur par son id
 *
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur/{id}', GetTroncQueteur::class);









