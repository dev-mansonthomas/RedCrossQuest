<?php




namespace RedCrossQuest\routes\routesActions\exportData;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\BusinessService\ExportDataBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class ExportData extends Action
{
  /**
   * @var QueteurDBService              $queteurDBService
   */
  private $queteurDBService;

  /**
   * @var ExportDataBusinessService     $exportDataBusinessService
   */
  private $exportDataBusinessService;

  /**
   * @var EmailBusinessService          $emailBusinessService
   */
  private $emailBusinessService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService $queteurDBService
   * @param ExportDataBusinessService $exportDataBusinessService
   * @param EmailBusinessService $emailBusinessService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              QueteurDBService              $queteurDBService,
                              ExportDataBusinessService     $exportDataBusinessService,
                              EmailBusinessService          $emailBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService           = $queteurDBService;
    $this->exportDataBusinessService  = $exportDataBusinessService;
    $this->emailBusinessService       = $emailBusinessService;
  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $ulId     = $this->decodedToken->getUlId();
   /*
    $this->validateSentData([
      ClientInputValidatorSpecs::withString("password", $this->getParam('password'), 40 , true )
    ]);
   $password      = $this->validatedData["password"];
   */

    $queteurId     = $this->decodedToken->getQueteurId();
    $queteurEntity = $this->queteurDBService->getQueteurById($queteurId, $ulId);
    $exportReport  = $this->exportDataBusinessService->exportData($ulId, null);

    $status = $this->emailBusinessService->sendExportDataUL($queteurEntity, $exportReport['fileName']);

    $this->response->getBody()->write(json_encode(new ExportDataResponse($status, $queteurEntity->email, $exportReport['fileName'],$exportReport['numberOfRows'])));

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



    return $this->response;
  }
}
