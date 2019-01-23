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
$app->get('/{role-id:[4-9]}/ul/{ul-id}/namedDonations', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = (int)$args['ul-id'];
    $roleId = (int)$args['role-id'];
    $params = $request->getQueryParams();


    $query   = array_key_exists('q'       ,$params)?$params['q'        ]:null;
    $deleted = array_key_exists('deleted' ,$params)?$params['deleted'  ]:false;
    $year    = array_key_exists('year'    ,$params)?$params['year'     ]:null;

    if($deleted=="true")
      $deleted=true;
    else
      $deleted=false;

    if(array_key_exists('admin_ul_id',$params) && $roleId > 4)
    {
      $adminUlId = $params['admin_ul_id'];
      //$this->logger->addInfo("Queteur list - UL ID:'$ulId' is overridden by superadmin to UL-ID: '$adminUlId' role ID:$roleId", array('decodedToken'=>$decodedToken));
      $ulId = $adminUlId;
    }

    $this->logger->addInfo("searching named donation", array('q'=>$query, 'deleted'=>$deleted, 'year'=>$year));
    $namedDonations = $this->namedDonationDBService->getNamedDonations($query, $deleted, $year, $ulId);

    $response->getBody()->write(json_encode($namedDonations));

    return $response;
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while fetching the NamedDonation for a year($year), deleted($deleted), query($query)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }

});

/**
 *
 * get one named donation
 *
 */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}', function ($request, $response, $args)
{
  $namedDonationEntity  = null;
  try
  {
    $ulId   = (int)$args['ul-id'];
    $id     = (int)$args['id'];
    $roleId = (int)$args['role-id'];

    $namedDonationEntity = $this->namedDonationDBService->getNamedDonationById($id, $ulId, $roleId);

    $response->getBody()->write(json_encode($namedDonationEntity));
    return $response;

  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while geting namedDonation $id", array("Exception"=>$e));
    throw $e;
  }
});

/**
 *
 * Update named donation
 *
 */
$app->put('/{role-id:[4-9]}/ul/{ul-id}/namedDonations/{id}', function ($request, $response, $args)
{
  $decodedToken         = $request->getAttribute('decodedJWT');
  $namedDonationEntity  = null;
  try
  {
    $ulId   = (int)$args['ul-id'];
    $userId = (int)$decodedToken->getUid ();

    $input                  = $request->getParsedBody();
    $namedDonationEntity    = new NamedDonationEntity($input, $this->logger);

    $this->namedDonationDBService->update($namedDonationEntity, $ulId, $userId);
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while updating namedDonation", array('namedDonationEntity'=>$namedDonationEntity, "Exception"=>$e));
    throw $e;
  }
  return $response;
});



/**
 * Create named donation
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/namedDonations', function ($request, $response, $args)
{
  $decodedToken         = $request->getAttribute('decodedJWT');
  $namedDonationEntity  = null;
  try
  {
    $ulId   = (int)$args['ul-id'];
    $userId = (int)$decodedToken->getUid ();

    $input                  = $request->getParsedBody();
    $namedDonationEntity    = new NamedDonationEntity($input, $this->logger);
    $namedDonationId        = $this->namedDonationDBService->insert($namedDonationEntity, $ulId, $userId);

    $response->getBody()->write(json_encode(array("namedDonationId"=>$namedDonationId), JSON_NUMERIC_CHECK));
  }
  catch(\Exception $e)
  {
    $this->logger->addError("error while creating named donation", array('namedDonationEntity'=>$namedDonationEntity, "Exception"=>$e));
    throw $e;
  }
  return $response;
});


