<?php
declare(strict_types=1);
require '../../vendor/autoload.php';

use RedCrossQuest\Middleware\AuthorisationMiddleware;
use Slim\App;

return function (App $app) {
  $app->add(AuthorisationMiddleware::class);
};
