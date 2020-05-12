<?php




namespace RedCrossQuest\routes\routesActions\yearlyGoals;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\YearlyGoalDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class CreateYearlyGoals extends Action
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

    $this->validateSentData([
      ClientInputValidatorSpecs::withInteger('year', $this->parsedBody, 2050, false)
    ]);

    $year  = $this->validatedData["year"];
    $this->yearlyGoalDBService->createYear($ulId, $year);

    return $this->response;
  }
}
