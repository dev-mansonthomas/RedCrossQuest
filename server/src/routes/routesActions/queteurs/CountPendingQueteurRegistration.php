<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


class CountPendingQueteurRegistration extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService          $queteurDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService          $queteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService = $queteurDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $ulId     = $this->decodedToken->getUlId();

    $count = $this->queteurDBService->countPendingQueteurRegistration($ulId);
    $this->response->getBody()->write(json_encode(new CountPendingRegistrationResponse($count)));

    return $this->response;
  }
}
