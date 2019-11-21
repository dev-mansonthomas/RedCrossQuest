<?php




namespace RedCrossQuest\routes\routesActions\mailing;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


class SendABatchOfMailing extends Action
{
  /**
   * @var UniteLocaleDBService $uniteLocaleDBService
   */
  private $uniteLocaleDBService;


  /**
   * @var EmailBusinessService $emailBusinessService
   */
  private $emailBusinessService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UniteLocaleDBService $uniteLocaleDBService
   * @param EmailBusinessService $emailBusinessService
   */
  public function __construct(LoggerInterface      $logger,
                              ClientInputValidator $clientInputValidator,
                              UniteLocaleDBService $uniteLocaleDBService,
                              EmailBusinessService $emailBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleDBService = $uniteLocaleDBService;
    $this->emailBusinessService = $emailBusinessService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $ulId     = $this->decodedToken->getUlId();

    $uniteLocaleEntity = $this->uniteLocaleDBService->getUniteLocaleById($ulId);
    $mailingReport     = $this->emailBusinessService->sendThanksEmailBatch($ulId, $uniteLocaleEntity);

    $this->response->getBody()->write(json_encode($mailingReport));

    return $this->response;
  }
}
