<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/4017
 * Time: 18:36
 */
require '../../vendor/autoload.php';

use RedCrossQuest\routes\routesActions\authentication\AuthenticateAction;
use RedCrossQuest\routes\routesActions\authentication\FirebaseAuthenticateAction;
use RedCrossQuest\routes\routesActions\authentication\GetUserInfoFromUUIDAction;
use RedCrossQuest\routes\routesActions\authentication\ListTroncs;
use RedCrossQuest\routes\routesActions\authentication\ResetPassword;
use RedCrossQuest\routes\routesActions\authentication\SendPasswordInitializationMailAction;

/********************************* Authentication ****************************************/

/**
 * Authenticate from Firebase with a JWT token from firebase.
 * This token is then checked and on success, the method will retrieve the info about the user and generate a RCQ JWT
 */
$app->post(getPrefix().'/firebase-authenticate', FirebaseAuthenticateAction::class);

/**
 * Authenticate from AngularJS old front-end with username/password against internal DB
 * and generate a RCQ JWT
 */
$app->post(getPrefix().'/authenticate'         , AuthenticateAction::class);

/**
 * for the user with the specified nivol, update the 'init_passwd_uuid' and 'init_passwd_date' field in DB
 * and send the user an email with a link, to reach the reset password form
 *
 */
$app->post(getPrefix().'/sendInit', SendPasswordInitializationMailAction::class);

/**
 * Get user information from the UUID
 * Used in the reinit password process
 */
$app->get(getPrefix().'/getInfoFromUUID', GetUserInfoFromUUIDAction::class);

/**
 * save new password of user
 */
$app->post(getPrefix().'/resetPassword', ResetPassword::class);
