<?php /** @noinspection SpellCheckingInspection */


namespace RedCrossQuest\routes\routesActions\exportData;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\BusinessService\ExportDataBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class ExportData extends Action
{
  /**
   * @var QueteurDBService              $queteurDBService
   */
  private QueteurDBService $queteurDBService;

  /**
   * @var ExportDataBusinessService     $exportDataBusinessService
   */
  private ExportDataBusinessService $exportDataBusinessService;

  /**
   * @var EmailBusinessService          $emailBusinessService
   */
  private EmailBusinessService $emailBusinessService;

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
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId          = $this->decodedToken->getUlId();
    $queteurId     = $this->decodedToken->getQueteurId();
    $queteurEntity = $this->queteurDBService->getQueteurById($queteurId, $ulId);
    $exportReport  = $this->exportDataBusinessService->exportData($ulId, null);
    $status        = $this->emailBusinessService->sendExportDataUL($queteurEntity, $exportReport['fileName']);

    $this->response->getBody()->write(json_encode(new ExportDataResponse($status, $queteurEntity->email, $exportReport['fileName'],$exportReport['numberOfRows'])));

    return $this->response;
  }
}
