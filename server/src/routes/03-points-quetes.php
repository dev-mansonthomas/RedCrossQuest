<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:36
 */

require '../../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\Entity\PointQueteEntity;
use RedCrossQuest\routes\routesActions\pointsQuetes\GetPointQuete;
use RedCrossQuest\routes\routesActions\pointsQuetes\ListPointsQuetes;
use RedCrossQuest\routes\routesActions\pointsQuetes\SearchPointsQuetes;
use RedCrossQuest\Service\Logger;

/********************************* POINT_QUETE ****************************************/

/**
 * fetch point de quete for an UL
 */
$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/pointQuetes/{id}', GetPointQuete::class);

$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/pointQuetes', ListPointsQuetes::class);

$app->get(getPrefix().'/{role-id:[1-9]}/ul/{ul-id}/pointQuetes/search', SearchPointsQuetes::class);




/**
 * update un point quete
 *
 * Dispo pour les roles de 2 à 9
 */
$app->put(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/pointQuetes/{id}', function (Request $request, Response $response, array $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  Logger::dataForLogging(new LoggingEntity($decodedToken));
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();

    $input               = $request->getParsedBody();
    $pointQueteEntity    = new PointQueteEntity($input, $this->get(LoggerInterface::class));

    $this->pointQueteDBService->update($pointQueteEntity, $ulId, $roleId);
  }
  catch(\Exception $e)
  {
    $this->get(LoggerInterface::class)->error("Error while updating point quete", array('decodedToken'=>$decodedToken, "Exception"=>$e, "pointQueteEntity"=>$pointQueteEntity));
    throw $e;
  }

  return $response;
});


/**
 * Crée un nouveau queteur
 */
$app->post(getPrefix().'/{role-id:[2-9]}/ul/{ul-id}/pointQuetes', function (Request $request, Response $response, array $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  Logger::dataForLogging(new LoggingEntity($decodedToken));
  try
  {
    $ulId                = $decodedToken->getUlId  ();
    $input               = $request->getParsedBody();
    $pointQueteEntity    = new PointQueteEntity($input, $this->get(LoggerInterface::class));

    $pointQueteId = $this->pointQueteDBService->insert            ($pointQueteEntity, $ulId);

    $response->getBody()->write(json_encode(array('pointQueteId' =>$pointQueteId), JSON_NUMERIC_CHECK));
  }
  catch(\Exception $e)
  {
    $this->get(LoggerInterface::class)->error("Error while creating a new PointQuete", array('decodedToken'=>$decodedToken, "Exception"=>$e, "pointQueteEntity"=>$pointQueteEntity));
    throw $e;
  }
  return $response;
});
