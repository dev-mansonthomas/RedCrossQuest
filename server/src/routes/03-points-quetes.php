<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:36
 */



/********************************* POINT_QUETE ****************************************/

$app->get('/ul/{ul-id}/pointQuetes', function ($request, $response, $args)
{
  try
  {
    $ulId = (int)$args['ul-id'];
    $this->logger->addInfo("Request UL ID '".$ulId."''");

    $this->logger->addInfo("Point Quete list");

    $mapper = new RedCrossQuest\PointQueteMapper($this->db, $this->logger);
    $params = $request->getQueryParams();

    $this->logger->addInfo("PointQuetes query");
    $pointQuetes = $mapper->getPointQuetes();


    $response->getBody()->write(json_encode($pointQuetes));

    return $response;

  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }

});

