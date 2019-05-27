<?php

namespace RedCrossQuest\BusinessService;


use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\DBService\NamedDonationDBService;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UniteLocaleSettingsDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\DBService\YearlyGoalDBService;

class ExportDataBusinessService
{
  protected $logger;
  /** @var QueteurDBService */
  protected $queteurDBService;
  /** @var PointQueteDBService */
  protected $pointQueteDBService;
  /** @var UserDBService */
  protected $userDBService;
  /** @var DailyStatsBeforeRCQDBService */
  protected $dailyStatsBeforeRCQDBService;
  /** @var TroncDBService */
  protected $troncDBService;
  /** @var NamedDonationDBService */
  protected $namedDonationDBService;

  /** @var TroncQueteurDBService */
  protected $troncQueteurDBService;
  /** @var UniteLocaleDBService */
  protected $uniteLocaleDBService;
  /** @var UniteLocaleSettingsDBService */
  protected $uniteLocaleSettingsDBService;
  /** @var YearlyGoalDBService */
  protected $yearlyGoalDBService;


  protected $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A' , 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                                        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I' , 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                                        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                                        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i' , 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                                        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y' , 'þ'=>'b', 'ÿ'=>'y', '/'=>'-', ':'=>'-', '?'=>' ' );


  public function __construct(\Slim\Container $c)
  {
    $this->logger                       = $c->logger                       ;
    $this->queteurDBService             = $c->queteurDBService             ;
    $this->userDBService                = $c->userDBService                ;
    $this->pointQueteDBService          = $c->pointQueteDBService          ;
    $this->dailyStatsBeforeRCQDBService = $c->dailyStatsBeforeRCQDBService ;
    $this->troncDBService               = $c->troncDBService               ;
    $this->namedDonationDBService       = $c->namedDonationDBService       ;
    $this->troncQueteurDBService        = $c->troncQueteurDBService        ;
    $this->uniteLocaleDBService         = $c->uniteLocaleDBService         ;
    $this->uniteLocaleSettingsDBService = $c->uniteLocaleSettingsDBService ;
    $this->yearlyGoalDBService          = $c->yearlyGoalDBService          ;
  }


  /**
   * Export UL data
   *
   * @param string password the password for the archive
   * @param integer $ulId  The ID of the Unité Locale
   * @param string $year  if 0, export all data, if not, export data from the specified year if applicable
   * @return array filename of the generated file, and the number of lines exported
   *
   * @throws \Exception   if something wrong happen
   */
  public function exportData(string $password, int $ulId, ?string $year)
  {

/**
   @@var Entity[][]
 */
    $exportData = [];

    $exportData['dailyStats']    = $this->dailyStatsBeforeRCQDBService->getDailyStats         ($ulId, $year);
    $exportData['namedDonation'] = $this->namedDonationDBService      ->getNamedDonations     (null, false, $year, $ulId);
    $exportData['pointQuete']    = $this->pointQueteDBService         ->getPointQuetes        ($ulId);
    $exportData['queteur']       = $this->queteurDBService            ->getQueteurs           ("", $ulId);
    $exportData['user']          = $this->userDBService               ->getULUsers            ($ulId);
    $exportData['troncs']        = $this->troncDBService              ->getTroncs             (null, $ulId, true, null);
    $exportData['troncQueteur']  = $this->troncQueteurDBService       ->getTroncsQueteurFromUL($ulId, $year);
    $exportData['ul']            = $this->uniteLocaleDBService        ->getUniteLocaleById    ($ulId);
    $exportData['yearlyGoal']    = $this->yearlyGoalDBService         ->getYearlyGoalsForExportData($ulId, $year);
    $nbOfLine=0;

    foreach ($exportData as $tableName => $oneDataTable)
    {
      $fileContent = "";
      if(is_array($oneDataTable))
      {
        if(count($oneDataTable)>=1)
        {
          $fileContent .= $oneDataTable[0]->generateCSVHeader();
          $nbOfLine++;
        }
        foreach($oneDataTable as $row)
        {
          $fileContent .= $row->generateCSVRow();
          $nbOfLine++;
        }
      }
      else
      {
        $fileContent .= $oneDataTable->generateCSVHeader();
        $nbOfLine++;
        $fileContent .= $oneDataTable->generateCSVRow();
        $nbOfLine++;
      }

      $this->logger->info("generating file "."/tmp/$ulId-".$tableName.".csv  $nbOfLine");
      file_put_contents("/tmp/$ulId-".$tableName.".csv",
        mb_convert_encoding($fileContent, "ISO-8859-1" , "ASCII, UTF-8" ));
    }

    try
    {
      $dateTime = date('Y-m-d-H-i-s', time());
      $ulNameForFileName = strtr(strtr($exportData['ul']->name, [' '=> '']), $this->unwanted_array );

      if(substr($ulNameForFileName, -1) == ".")
      {
        $ulNameForFileName = substr($ulNameForFileName, 0, strlen($ulNameForFileName) - 1);
      }

      $zipFileName = "$dateTime-RedCrossQuest-DataExport-UL-$ulId".($year!= null? $year.'':'')."-".$ulNameForFileName.".zip";

      $zipFilePath = "/tmp/".$zipFileName;
      $z = new \ZipArchive();
      $zipFileOpen = ($z->open($zipFilePath, \ZipArchive::CREATE));
      if(true === $zipFileOpen)
      {
        $z->setPassword($password);
        $archiveComment = strtr( 'RedCrossQuest Data Export - '.$dateTime .' for UL - '.$ulId.' - '.$exportData['ul']->name.($year!= null? ' for year :'.$year.'':''), $this->unwanted_array );
        $z->setArchiveComment($archiveComment );

        foreach ($exportData as $tableName => $oneDataTable)
        {
          $filename = $ulId."-".$tableName.".csv";
          $z->addFile("/tmp/$filename", $filename);
          $z->setEncryptionName($filename,  \ZipArchive::EM_AES_256 , $password);
        }
        //csv files must not be deleted before closing the zip, otherwise we get file not found. as if the zip file is built on the close command
        $z->close();

        foreach ($exportData as $tableName => $oneDataTable)
        {
          $filename = $ulId."-".$tableName.".csv";
          unlink("/tmp/$filename");
        }
      }
      else
      {
        $this->logger->info("failed opening Zip",["zipFileOpen"=>$zipFileOpen]);
      }
    }
    catch(\Exception $e)
    {
      $this->logger->error("Error while Exporting Data", ["YEAR" => $year, "Exception" => $e]);
      throw $e;
    }

    return ["fileName" => $zipFileName, "numberOfRows"=> $nbOfLine];
  }
}
