<?php

namespace RedCrossQuest\BusinessService;


use Exception;
use Psr\Log\LoggerInterface;
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
use ZipArchive;

class ExportDataBusinessService
{
  /** @var LoggerInterface $logger */
  protected $logger;
  /** @var QueteurDBService $queteurDBService*/
  protected $queteurDBService;
  /** @var PointQueteDBService $pointQueteDBService*/
  protected $pointQueteDBService;
  /** @var UserDBService $userDBService*/
  protected $userDBService;
  /** @var DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService*/
  protected $dailyStatsBeforeRCQDBService;
  /** @var TroncDBService $troncDBService*/
  protected $troncDBService;
  /** @var NamedDonationDBService $namedDonationDBService*/
  protected $namedDonationDBService;
  /** @var TroncQueteurDBService $troncQueteurDBService */
  protected $troncQueteurDBService;
  /** @var UniteLocaleDBService $uniteLocaleDBService*/
  protected $uniteLocaleDBService;
  /** @var UniteLocaleSettingsDBService $uniteLocaleSettingsDBService*/
  protected $uniteLocaleSettingsDBService;
  /** @var YearlyGoalDBService $yearlyGoalDBService*/
  protected $yearlyGoalDBService;


  protected $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A' , 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                                        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I' , 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                                        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                                        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i' , 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                                        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y' , 'þ'=>'b', 'ÿ'=>'y', '/'=>'-', ':'=>'-', '?'=>' ' );


  public function __construct(  LoggerInterface $logger ,
                                QueteurDBService $queteurDBService,
                                PointQueteDBService $pointQueteDBService,
                                UserDBService $userDBService,
                                DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService,
                                TroncDBService $troncDBService,
                                NamedDonationDBService $namedDonationDBService,
                                TroncQueteurDBService $troncQueteurDBService ,
                                UniteLocaleDBService $uniteLocaleDBService,
                                UniteLocaleSettingsDBService $uniteLocaleSettingsDBService,
                                YearlyGoalDBService $yearlyGoalDBService
  )
  {
    $this->logger                       = $logger                       ;
    $this->queteurDBService             = $queteurDBService             ;
    $this->userDBService                = $userDBService                ;
    $this->pointQueteDBService          = $pointQueteDBService          ;
    $this->dailyStatsBeforeRCQDBService = $dailyStatsBeforeRCQDBService ;
    $this->troncDBService               = $troncDBService               ;
    $this->namedDonationDBService       = $namedDonationDBService       ;
    $this->troncQueteurDBService        = $troncQueteurDBService        ;
    $this->uniteLocaleDBService         = $uniteLocaleDBService         ;
    $this->uniteLocaleSettingsDBService = $uniteLocaleSettingsDBService ;
    $this->yearlyGoalDBService          = $yearlyGoalDBService          ;
  }


  /**
   * Export UL data
   *
   * @param integer $ulId  The ID of the Unité Locale
   * @param string $year  if 0, export all data, if not, export data from the specified year if applicable
   * @return array filename of the generated file, and the number of lines exported
   *
   * @throws Exception   if something wrong happen
   */
  public function exportData(int $ulId, ?string $year):array
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

      $this->logger->info("generating file ".sys_get_temp_dir()."/$ulId-".$tableName.".csv  $nbOfLine");
      file_put_contents(sys_get_temp_dir()."/$ulId-".$tableName.".csv",
        mb_convert_encoding($fileContent, "ISO-8859-1" , "ASCII, UTF-8" ));

      $this->logger->info("file  generated with stats ".sys_get_temp_dir()."/$ulId-".$tableName.".csv  $nbOfLine",["fileInfo"=>stat (sys_get_temp_dir()."/$ulId-".$tableName.".csv")]);
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

      $zipFilePath = sys_get_temp_dir()."/".$zipFileName;
      $z = new ZipArchive();
      $zipFileOpen = ($z->open($zipFilePath, ZipArchive::CREATE));
      if(true === $zipFileOpen)
      {
        //$z->setPassword($password);
        $archiveComment = strtr( 'RedCrossQuest Data Export - '.$dateTime .' for UL - '.$ulId.' - '.$exportData['ul']->name.($year!= null? ' for year :'.$year.'':''), $this->unwanted_array );
        $z->setArchiveComment($archiveComment );

        foreach ($exportData as $tableName => $oneDataTable)
        {
          $filename = $ulId."-".$tableName.".csv";
          $z->addFile(sys_get_temp_dir()."/$filename", $filename);
          //$z->setEncryptionName($filename,  ZipArchive::EM_AES_256 , $password);
        }
        //csv files must not be deleted before closing the zip, otherwise we get file not found. as if the zip file is built on the close command
        $z->close();

        $this->logger->info("zip file generated with stats $zipFilePath",["fileInfo"=>stat ($zipFilePath)]);

        foreach ($exportData as $tableName => $oneDataTable)
        {
          $filename = $ulId."-".$tableName.".csv";
          unlink(sys_get_temp_dir()."/$filename");
        }
      }
      else
      {
        $this->logger->info("failed opening Zip",["zipFileOpen"=>$zipFileOpen]);
      }
    }
    catch(Exception $e)
    {
      $this->logger->error("Error while Exporting Data", ["YEAR" => $year, "Exception" => $e]);
      throw $e;
    }

    return ["fileName" => $zipFileName, "numberOfRows"=> $nbOfLine];
  }
}
