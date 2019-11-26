<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:35
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\troncQueteurHistory\GetTroncQueteurHistoryFromTQID;


/********************************* TRONC_QUETEUR ****************************************/


/**
 * récupère l'historique d'un tronc_queteur par son id
 *
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/tronc_queteur_history', GetTroncQueteurHistoryFromTQID::class);




