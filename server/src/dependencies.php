<?php
// DIC configuration
$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c)
{
  $settings = $c->get('settings')['renderer'];
  return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c)
{
  $settings = $c->get('settings')['logger'];

  $logger = new Monolog\Logger($settings['name']);
  $logger->pushProcessor(new Monolog\Processor\UidProcessor());
  $logger->pushHandler  (new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));

  return $logger;
};
// DB connection
$container['db'] = function ($c)
{
  $db = $c['settings']['db'];
  $pdo = new PDO("mysql:host=".$db['host'].";dbname=".$db['dbname'].";charset=utf8", $db['user'], $db['pwd']);

  $pdo->setAttribute(PDO::ATTR_ERRMODE           , PDO::ERRMODE_EXCEPTION );
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC       );

  return $pdo;
};

//email

$container['mailer'] = function ($c)
{
  $settings = $c['settings']['email'];

  $mailer = new PHPMailer();

  $mailer->Host       = $settings['server'];
  $mailer->isSMTP();
  $mailer->SMTPAuth   = true;
  $mailer->SMTPSecure = 'ssl';
  $mailer->Port       = $settings['port'];
	$mailer->Username   = $settings['username'];
	$mailer->Password   = $settings['password'];
	$mailer->isHTML(true);
  $mailer->CharSet = 'UTF-8';

  // ca écrit sur la "page", donc dans la réponse JSON. (utiliser output_buffering pour capturer)
	//$mailer->SMTPDebug = 3;

  return new \RedCrossQuest\BusinessService\EmailBusinessService($c->logger, $mailer, $c['settings']['appSettings']);
};
