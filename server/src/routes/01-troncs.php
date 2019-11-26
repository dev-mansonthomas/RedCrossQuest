<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:33
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\troncs\GetTronc;
use RedCrossQuest\routes\routesActions\troncs\InsertTronc;
use RedCrossQuest\routes\routesActions\troncs\ListTroncs;
use RedCrossQuest\routes\routesActions\troncs\UpdateTronc;

/********************************* TRONC ****************************************/

/***
 * récupère la liste des troncs (avec critères de recherche)
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/troncs', ListTroncs::class);

/**
 * récupère le détails d'un tronc (a enrichir avec les troncs_queteurs associés)
 *
 * */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/troncs/{id}', GetTronc::class);

/**
 * Update le tronc, seulement pour l'admin
 *
 * */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/troncs/{id}', UpdateTronc::class);

/**
 * Insert le tronc, seulement pour l'admin
 *
 * */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/troncs', InsertTronc::class);


