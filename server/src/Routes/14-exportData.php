<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:38
 */

use RedCrossQuest\BusinessService\ExportDataBusinessService;


/**
 * Export Data of the Unite Local to a file and download it
 *
 * Dispo pour le role admin local
 */
$app->get('/{role-id:[4-9]}/ul/{ul-id}/exportData', function ($request, $response, $args)
{
  $decodedToken = $request->getAttribute('decodedJWT');
  try
  {
    $ulId     = (int)$args['ul-id'   ];
    $password = trim($request->getParsedBodyParam("password"));

    $exportDataBusinessService = new ExportDataBusinessService(
      $this->logger                                                                        ,
      new \RedCrossQuest\DBService\QueteurDBService              ($this->db, $this->logger),
      new \RedCrossQuest\DBService\UserDBService                 ($this->db, $this->logger),
      new \RedCrossQuest\DBService\PointQueteDBService           ($this->db, $this->logger),
      new \RedCrossQuest\DBService\DailyStatsBeforeRCQDBService  ($this->db, $this->logger),
      new \RedCrossQuest\DBService\TroncDBService                ($this->db, $this->logger),
      new \RedCrossQuest\DBService\NamedDonationDBService        ($this->db, $this->logger),
      new \RedCrossQuest\DBService\TroncQueteurDBService         ($this->db, $this->logger),
      new \RedCrossQuest\DBService\UniteLocaleDBService          ($this->db, $this->logger),
      new \RedCrossQuest\DBService\UniteLocaleSettingsDBService  ($this->db, $this->logger),
      new \RedCrossQuest\DBService\YearlyGoalDBService           ($this->db, $this->logger)
    );



    $zipFileName = $exportDataBusinessService->exportData($password, $ulId, null);

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


  }
  catch(\Exception $e)
  {
    $this->logger->addError("Error while fetching the Mailing Summary for UL ($ulId)", array('decodedToken'=>$decodedToken, "Exception"=>$e));
    throw $e;
  }
});



