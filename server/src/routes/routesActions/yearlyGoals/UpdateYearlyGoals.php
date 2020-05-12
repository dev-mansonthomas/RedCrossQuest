<?php




namespace RedCrossQuest\routes\routesActions\yearlyGoals;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\YearlyGoalDBService;
use RedCrossQuest\Entity\YearlyGoalEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class UpdateYearlyGoals extends Action
{
  /**
   * @var YearlyGoalDBService $yearlyGoalDBService
   */
  private $yearlyGoalDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param YearlyGoalDBService $yearlyGoalDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              YearlyGoalDBService           $yearlyGoalDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->yearlyGoalDBService = $yearlyGoalDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId     = $this->decodedToken->getUlId();

    $yearlyGoalEntity = new YearlyGoalEntity($this->parsedBody, $this->logger);
    $this->yearlyGoalDBService->update($yearlyGoalEntity, $ulId);


    return $this->response;
  }
}
