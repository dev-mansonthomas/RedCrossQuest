<?php

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;


return function (ContainerInterface $c, App $app) {


  $customErrorHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
  ) use ($c, $app) {


    $logger = $c->get(LoggerInterface::class);

    $logger->error("errorHandler: An Error Occurred",
      array(
        'URI'      => $request->getUri    (),
        'headers'  => $request->getHeaders(),
        'body'     => $request->getBody   ()->getContents(),
        'exception'=> $exception));


    $payload = ['error' => $exception->getMessage()];

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
      json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response;
  };


  return $customErrorHandler;


};
