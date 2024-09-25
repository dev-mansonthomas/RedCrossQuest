<?php
error_reporting(E_ALL );
require __DIR__ . '/../../vendor/autoload.php';

use Psr\Log\LoggerInterface;
use RedCrossQuest\Service\SlackService;
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;

if (PHP_SAPI == 'cli-server')
{
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file))
    {
        return false;
    }
}
//REST services do not need server sessions
//session_start();

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();
// use annotation to inject settings
$containerBuilder->useAttributes(true);
$containerBuilder->enableCompilation (sys_get_temp_dir().'/cache');

// Set up settings
$settings = require __DIR__ . '/../../src/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../../src/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

$loggerInterface = $container->get(LoggerInterface::class);
$slackService    = $container->get(SlackService::class);
$loggerInterface->setSlackService ($slackService);
$slackService   ->setLogger       ($loggerInterface);


// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();



// Register middleware
$middleware = require __DIR__ . '/../../src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../../src/routes.php';
$routes($app);


// Add Routing Middleware
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->setBasePath('/rest');
$errorHandler = require __DIR__ . '/../../src/errorHandler.php';
$customErrorHandler = $errorHandler($container, $app);

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

// Run app
$app->run();
