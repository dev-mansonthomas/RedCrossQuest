<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

require '../../vendor/autoload.php';

use \RedCrossQuest\Service\Logger;
use \RedCrossQuest\Entity\LoggingEntity;



/**
 * Export Data of the Unite Local to a file and download it
 *
 * Dispo pour le role admin local
 */
$app->get(getPrefix().'/{role-id:[4-9]}/ul/{ul-id}/exportData', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  Logger::dataForLogging(new LoggingEntity($decodedToken));
  try
  {
    $params   = $request->getQueryParams();
    $ulId     = $decodedToken->getUlId  ();
    $queteurId= $decodedToken->getQueteurId();
    $password = $this->clientInputValidator->validateString("password", getParam($params,'password'), 40 , true );

    $queteurEntity = $this->queteurDBService->getQueteurById($queteurId, $ulId);
    $exportReport  = $this->exportDataBusinessService->exportData($password, $ulId, null);

    $status = $this->mailer->sendExportDataUL($queteurEntity, $exportReport['fileName']);

    return $response->getBody()->write(json_encode(["status"=>$status, "email" => $queteurEntity->email, "fileName"=>$exportReport['fileName'], "numberOfRows" => $exportReport['numberOfRows']]));
    /*  envoie bien le fichier comme il faut, mais ne fonctionne pas en rest

    $fh = fopen("/tmp/".$zipFileName, 'r  ');
    $stream = new \Slim\Http\Stream($fh);
    return $response->withHeader('Content-Type', 'application/force-download')
      ->withHeader('Content-Type', 'application/octet-stream')
      ->withHeader('Content-Type', 'application/download')
      ->withHeader('Content-Description', 'File Transfer')
      ->withHeader('Content-Transfer-Encoding', 'binary')
      ->withHeader('Content-Disposition', 'attachment; filename="' .$zipFileName . '"')
      ->withHeader('Expires', '0')
      ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
      ->withHeader('Pragma', 'public')
      ->withBody($stream);
      */

  }
  catch(\Exception $e)
  {
    $this->logger->error("Error while fetching the Mailing Summary for UL ($ulId)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



