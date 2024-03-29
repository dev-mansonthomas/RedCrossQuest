<?php /** @noinspection ALL */

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RedCrossQuest\Service\Logger;
use Slim\App;


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
