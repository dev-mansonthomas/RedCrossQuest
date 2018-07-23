<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use \RedCrossQuest\DBService\MailingDBService;
use \RedCrossQuest\DBService\UniteLocaleDBService;


/**
 * Get summary info about mailing
 *
 * Dispo pour le role admin local
 */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/mailing', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId   = (int)$args['ul-id'];

    $mailingDBService = new MailingDBService($this->db, $this->logger);
    $mailingSummary = $mailingDBService->getMailingSummary($ulId);

    $response->getBody()->write(json_encode($mailingSummary));

    return $response;
  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while fetching the Mailing Summary for UL ($ulId)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



/**
 * Send a batch of mailing
 */
$app->post('/{role-id:[4-9]}/ul/{ul-id}/mailing', function ($request, $response, $args)
{
  $decodedToken         = $request->getAttribute('decodedJWT');
  $namedDonationEntity  = null;
  try
  {
    $ulId   = (int)$args['ul-id'];
    $userId = (int)$decodedToken->getUid ();

    $uniteLocalDBService = new UniteLocaleDBService($this->db, $this->logger);
    $uniteLocaleEntity = $uniteLocalDBService->getUniteLocaleById($ulId);

    $mailingReport = $this->mailer->sendThanksEmailBatch($ulId, $uniteLocaleEntity);

    $response->getBody()->write(json_encode($mailingReport));
  }
  catch(\Exception $e)
  {
    $this->logger->addError("error while sending batch email", array('ulId'=>$ulId, "Exception"=>$e));
    throw $e;
  }
  return $response;
});


/**
 * Confirm the opening of a spotfire dashboard for a queteur
 */
$app->post('/thanks_mailing/{guid}', function ($request, $response, $args)
{
  try
  {
    $guid   = $args['guid'];

    $mailingDBService = new MailingDBService($this->db, $this->logger);
    $mailingDBService->confirmRead($guid);

    $response->getBody()->write(json_encode($guid));
  }
  catch(\Exception $e)
  {
    $this->logger->addError("error while marking spotifre report as read (Merci) for guid", array('guid'=>$guid, "Exception"=>$e));
    throw $e;
  }
  return $response;
});


