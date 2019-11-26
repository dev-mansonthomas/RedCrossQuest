<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\users\CreateUser;
use RedCrossQuest\routes\routesActions\users\ReInitUserPassword;
use RedCrossQuest\routes\routesActions\users\UpdateUser;

/********************************* USERS ****************************************/



$app->put (getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/users/{id}'               , UpdateUser::class);
$app->put (getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/users/{id}/reInitPassword', ReInitUserPassword::class);
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/users'                    , CreateUser::class);
