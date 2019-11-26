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


$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/queteurs'                                , ListQueteurs::class);
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/queteurs/countPendingQueteurRegistration', CountPendingQueteurRegistration::class);
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/queteurs/listPendingQueteurRegistration' , ListPendingQueteurRegistration::class);
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/queteurs/searchSimilarQueteurs'          , SearchSimilarQueteurs::class);

$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}'                           , GetQueteur::class);
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/queteurs/{id}/getQueteurRegistration'    , GetQueteurRegistration::class);

$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs/markAllAsPrinted'               , MarkAllQueteurQRCodeAsPrinted::class);


$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}',                                          UpdateQueteur::class);
$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}/anonymize'                               , AnonymizeQueteur::class);
$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs/{id}/associateRegistrationWithExistingQueteur', AssociateRegistrationWithExistingQueteur::class);


$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs'                                             , CreateQueteur::class);
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/queteurs/approveQueteurRegistration'                  , ApproveQueteurRegistration::class);



