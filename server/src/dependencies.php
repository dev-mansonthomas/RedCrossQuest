<?php

use SendGrid\Email;
use \RedCrossQuest\BusinessService\EmailBusinessService;
use \RedCrossQuest\DBService\MailingDBService;

// DIC configuration
$container = $app->getContainer();

// view renderer
$container['renderer'] = function (\Slim\Container $c)
{
  $settings = $c->get('settings')['renderer'];
  return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function (\Slim\Container $c)
{
  $settings = $c->get('settings')['logger'];

  $logger = new Monolog\Logger($settings['name']);
  $logger->pushProcessor(new Monolog\Processor\UidProcessor());
  $logger->pushHandler  (new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::INFO));

  return $logger;
};
// DB connection
$container['db'] = function (\Slim\Container $c)
{
  $db = $c['settings']['db'];

  try
  {
    $pdo = new PDO( $db['dsn'], $db['user'], $db['pwd']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE           , PDO::ERRMODE_EXCEPTION );
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC       );
    return $pdo;

  }
  catch(\Exception $e)
  {
    $logger = $c->get('logger');
    $logger->addError("Error while connecting to DB with parameters", array("dsn"=>$db['dsn'],'user'=>$db['user'],'pwd'=>strlen($db['pwd']), 'exception'=>$e));
    throw $e;
  }
};

//email

$container['mailer'] = function (\Slim\Container $c)
{
  $settings = $c['settings']['appSettings']['email'];

  $sendgrid       = new SendGrid($settings['sendgrid.api_key']);
  $sendgridSender = new Email("RedCrossQuest", $settings['sendgrid.sender']);

  return new EmailBusinessService($c->logger, $sendgrid, $sendgridSender, $c['settings']['appSettings'], new MailingDBService($c->db, $c->logger));
};


$c['errorHandler'] = function (\Slim\Container $c) {
  return function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, \Exception $exception) use ($c)
  {
    $logger = $c->get('logger');

    $logger->addError("An Error Occured",
      array(
        'URI'     => $request->getUri(),
        'headers' => $request->getHeaders(),
        'body'    => $request->getBody()->getContents(),
        'exception'=>$exception)
    );


    return $c['response']->withStatus(500)
      ->withHeader('Content-Type', 'text/html')
      ->write('Something went wrong! - '.$exception->getMessage());
  };
};