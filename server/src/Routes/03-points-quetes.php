<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:36
 */

use \RedCrossQuest\DBService\PointQueteDBService;

/********************************* POINT_QUETE ****************************************/

/**
 * fetch point de quete for an UL
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/pointQuetes/{id}', function ($request, $response, $args) {
  $decodedToken = $request->getAttribute('decodedJWT');
  $ulId   = (int)$args['ul-id'];
  $id     = (int)$args['id'   ];

  try
  {
    $pointQueteDBService = new PointQueteDBService($this->db, $this->logger);

    $pointQuete = $pointQueteDBService->getPointQueteById($id, $ulId);

    $response->getBody()->write(json_encode($pointQuete));

    return $response;

  }
  catch(\Exception $e)
  {
    $this->logger->addError("error while getting point_quete '$id' of ul with id $ulId ", array('decodedToken'=>json_encode($decodedToken), "Exception"=>json_encode($e)));
    throw $e;
  }


});


  /**
 * fetch point de quete for an UL
 */
$app->get('/{role-id:[1-9]}/ul/{ul-id}/pointQuetes', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');

 try
  {
    $ulId   = (int)$args['ul-id'  ];
    $roleId = (int)$args['role-id'];

    $pointQueteDBService = new PointQueteDBService($this->db, $this->logger);

    $params = $request->getQueryParams();

    $action = array_key_exists('action',$params)?$params['action']:null;

    if( $action === "search")
    {//admin function
      if(array_key_exists('admin_ul_id',$params) && $roleId == 9)
      {
        $adminUlId = $params['admin_ul_id'];
        //$this->logger->addInfo("Queteur list - UL ID:'$ulId' is overridden by superadmin to UL-ID: '$adminUlId' role ID:$roleId", array('decodedToken'=>$decodedToken));
        $ulId = $adminUlId;
      }

      $query            = array_key_exists('q'                ,$params)?$params['q'               ]:null;
      $point_quete_type = array_key_exists('point_quete_type' ,$params)?$params['point_quete_type']:null;
      $active           = array_key_exists('active'           ,$params)?$params['active'          ]:1;

      if($ulId == null || $ulId == '')
      {
        $ulId = (int)$decodedToken->getUlId();
      }

      //$this->logger->addInfo("PointQuetes query for ul: $ulId");
      $pointQuetes = $pointQueteDBService->searchPointQuetes($query, $point_quete_type, $active, $ulId);
    }
    else
    {//used for the dropdown to select point de quete while preparing a tronc
      $pointQuetes = $pointQueteDBService->getPointQuetes($ulId);
    }



    $response->getBody()->write(json_encode($pointQuetes));
    return $response;

  }
  catch(Exception $e)
  {
    $this->logger->addError("error while getting point_quete of ul with id $ulId ", array('decodedToken'=>json_encode($decodedToken), "Exception"=>json_encode($e)));
    throw $e;
  }

});

