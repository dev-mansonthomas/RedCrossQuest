<?php
error_reporting(E_ALL);

// --- FAIL-FAST: reject obvious scans before DI container boot ---
// Avoids DI init + Slack post on every vulnerability probe.
// Rules are deliberately narrow: paths here are never valid for RCQ.
(function (): void {
  $uri  = $_SERVER['REQUEST_URI']     ?? '';
  $path = parse_url($uri, PHP_URL_PATH) ?: $uri;
  $ua   = $_SERVER['HTTP_USER_AGENT'] ?? '';

  // 1. unsubstituted Angular $resource placeholders (e.g. /rest/:roleId/...)
  if (str_contains($path, '/:')) { http_response_code(404); exit; }

  // 2. known third-party product probes (Jira / XWiki / Magento / misc)
  static $scanPrefixes = [
    '/rest/api-docs', '/rest/wikis/', '/rest/id-pools/', '/rest/getPlaylists',
    '/rest/V1/', '/rest/rights/', '/rest/users/1/settings',
    '/rest/issueNav/', '/rest/xxxxxxxxxxxxxxx/',
  ];
  foreach ($scanPrefixes as $p) {
    if (str_starts_with($path, $p)) { http_response_code(404); exit; }
  }

  // 3. UA-based blocklist (belt + suspenders with GAE firewall rules)
  if (str_contains($ua, 'research.hadrian.io')) { http_response_code(404); exit; }

  // 4. POST/PUT with empty body on /rest/* : every legit write-endpoint expects
  //    a payload. Scanners probing endpoints with empty bodies (Content-Length: 0
  //    or missing) trigger downstream validation errors -> 500 -> Slack noise.
  $method = $_SERVER['REQUEST_METHOD'] ?? '';
  if ($method === 'POST' || $method === 'PUT') {
    $cl = $_SERVER['CONTENT_LENGTH'] ?? null;
    if ($cl === null || $cl === '' || $cl === '0') { http_response_code(400); exit; }
  }
})();
// --- /FAIL-FAST ---

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
