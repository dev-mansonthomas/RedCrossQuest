<?php /** @noinspection ALL */

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RedCrossQuest\Service\Logger;
use Slim\App;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;


return function (ContainerInterface $c, App $app)
{
  return function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
  ) use ($c, $app) {


    $logger = $c->get(LoggerInterface::class);

    // Routing exceptions (404/405) are normal - usually vulnerability scans or
    // clients probing unknown endpoints. Log at warning level so they do not
    // trigger Slack alerts (only error/critical/alert/emergency are forwarded).
    if ($exception instanceof HttpNotFoundException
     || $exception instanceof HttpMethodNotAllowedException)
    {
      $isMethodNotAllowed = $exception instanceof HttpMethodNotAllowedException;
      $status             = $isMethodNotAllowed ? 405 : 404;
      $errorCode          = $isMethodNotAllowed ? 'method_not_allowed' : 'not_found';

      $logger->warning("Routing: ".$request->getMethod()." ".$request->getUri()." -> ".$status,
        array(
          'URI'        => (string)$request->getUri(),
          'httpMethod' => $request->getMethod(),
          'remoteIp'   => $_SERVER['REMOTE_ADDR']     ?? '',
          'userAgent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ));

      $response = $app->getResponseFactory()->createResponse($status);
      $response->getBody()->write(json_encode(['error' => $errorCode], JSON_UNESCAPED_UNICODE));
      return $response;
    }

    $logger->error("Generic Error Handler - Untrapped exception reached this error handler",
      array(
        'URI'       => $request->getUri    (),
        'httpMethod'=> $request->getMethod (),
        'headers'   => $request->getHeaders(),
        'bodyType'          => gettype($request->getBody   ()),
        'bodyClass'         => get_class($request->getBody   ()),
        'bodyContentType'   => gettype($request->getBody   ()->getContents()),
        'body'              => gettype($request->getBody   ()) == "object" ? print_r($request->getBody   (), true): $request->getBody   ()->getContents(),
        Logger::$EXCEPTION => $exception));


    $payload = ['error' => $exception->getMessage()];

    $response = $app->getResponseFactory()->createResponse(500);
    $response->getBody()->write(
      json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response;
  };


};
