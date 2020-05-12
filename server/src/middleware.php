<?php
declare(strict_types=1);

use RedCrossQuest\Middleware\AuthorisationMiddleware;
use Slim\App;

return function (App $app) {
  $app->add(AuthorisationMiddleware::class);
};
