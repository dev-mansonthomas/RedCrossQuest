<?php




namespace RedCrossQuest\routes\routesActions\dailyStats;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class CreateYearOfDailyStats extends Action
{
  /**
   * @var DailyStatsBeforeRCQDBService          $dailyStatsBeforeRCQDBService
   */
  private $dailyStatsBeforeRCQDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              DailyStatsBeforeRCQDBService  $dailyStatsBeforeRCQDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->dailyStatsBeforeRCQDBService = $dailyStatsBeforeRCQDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $ulId = $this->decodedToken->getUlId();
    $this->validateSentData([
      ClientInputValidatorSpecs::withInteger('year', $this->getParam('year'), 2050, false)
    ]);

    $year  = $this->validatedData["year"];

    $this->dailyStatsBeforeRCQDBService->createYear($ulId, $year);

    return $this->response;
  }
}
