<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';


use \RedCrossQuest\Entity\NamedDonationEntity;


/**
 * Fetch the named donation of an UL
 *
 * Dispo pour le role admin local
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/namedDonations', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();

    $params = $request->getQueryParams();

    $this->logger->info("params ".getParam($params,'deleted'         ), array("params"=>$params));

    $query   = $this->clientInputValidator->validateString ("q"       , getParam($params,'q'               ), 100  , false );
    $deleted = $this->clientInputValidator->validateBoolean("deleted" , getParam($params,'deleted'         ), false, false );
    $year    = $this->clientInputValidator->validateInteger('year'    , getParam($params,'year'            ), 2050 , false );


    if(array_key_exists('admin_ul_id',$params) && $roleId == 9)
    {
      $ulId = $this->clientInputValidator->validateInteger('admin_ul_id', $args['admin_ul_id'], 1000, true);
      //$this->logger->info("NamedDonation list - UL ID:'".$decodedToken->getUlId  ()."' is overridden by superadmin to UL-ID: '$adminUlId' role ID:$roleId", array('decodedToken'=>$decodedToken));
    }


    $this->logger->info("searching named donation", array('q'=>$query, 'deleted'=>$deleted, 'year'=>$year));
    $namedDonations = $this->namedDonationDBService->getNamedDonations($query, $deleted, $year, $ulId);

    return $response->getBody()->write(json_encode($namedDonations));
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while fetching the NamedDonation for a year($year), deleted($deleted), query($query)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }

});

/**
 *
 * get one named donation
 *
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}', function ($request, $response, $args)
{
  $namedDonationEntity  = null;
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $roleId = $decodedToken->getRoleId();

    $id     = $this->clientInputValidator->validateInteger('id', $args['id'], 1000000, true);

    $namedDonationEntity = $this->namedDonationDBService->getNamedDonationById($id, $ulId, $roleId);

    return $response->getBody()->write(json_encode($namedDonationEntity));
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while geting namedDonation $id", array("Exception"=>$e));
    throw $e;
  }
});

/**
 *
 * Update named donation
 *
 */
$app->put(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}', function ($request, $response, $args)
{
  $decodedToken         = $request->getAttribute('decodedJWT');
  $namedDonationEntity  = null;
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $userId = $decodedToken->getUid   ();

    $input                  = $request->getParsedBody();
    $namedDonationEntity    = new NamedDonationEntity($input, $this->logger);

    $this->namedDonationDBService->update($namedDonationEntity, $ulId, $userId);
  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while updating namedDonation", array('namedDonationEntity'=>$namedDonationEntity, "Exception"=>$e));
    throw $e;
  }
  return $response;
});



/**
 * Create named donation
 */
$app->post(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/namedDonations', function ($request, $response, $args)
{
  $decodedToken         = $request->getAttribute('decodedJWT');
  $namedDonationEntity  = null;
  try
  {
    $ulId   = $decodedToken->getUlId  ();
    $userId = $decodedToken->getUid   ();

    $input                  = $request->getParsedBody();
    $namedDonationEntity    = new NamedDonationEntity($input, $this->logger);
    $namedDonationId        = $this->namedDonationDBService->insert($namedDonationEntity, $ulId, $userId);

    $response->getBody()->write(json_encode(array("namedDonationId"=>$namedDonationId), JSON_NUMERIC_CHECK));
  }
  catch(\Exception $e)
  {
    $this->logger->error("error while creating named donation", array('namedDonationEntity'=>$namedDonationEntity, "Exception"=>$e));
    throw $e;
  }
  return $response;
});


