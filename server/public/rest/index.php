<?php
error_reporting(E_ALL);

// Silence the `getLabel is deprecated` E_USER_WARNING raised by the
// protobuf 4.x FieldDescriptor, called from google/gax 1.36 Serializer.
// It is emitted during the shutdown handler of LoggingClient::psrBatchLogger
// (after the Slim response is sent) and pollutes the HTTP body. Drop this
// handler once google/gax is upgraded to a version that no longer calls
// the deprecated method.
set_error_handler(static function (int $errno, string $errstr): bool {
    if ($errno === E_USER_WARNING && str_contains($errstr, 'getLabel is deprecated')) {
        return true;
    }
    return false;
});

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

//don't use sys_get_temp_dir() for the cache, as a new folder is generated for each call, it's not a cache then
$compiledPath = '/tmp/php-di-compiled';
$containerBuilder->enableCompilation($compiledPath);

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
