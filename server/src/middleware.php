<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 03/04/2017
 * Time: 11:32
 */

include_once ("../../src/Middleware/AuthorisationMiddleware.php");
use \RedCrossQuest\Middleware\AuthorisationMiddleware;

$app->add( new AuthorisationMiddleware($app) );
