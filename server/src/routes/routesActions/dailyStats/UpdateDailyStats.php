<?php




namespace RedCrossQuest\routes\routesActions\dailyStats;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\Entity\DailyStatsBeforeRCQEntity;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


class UpdateDailyStats extends Action
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
    $ulId                         = $this->decodedToken->getUlId();
    $dailyStatsBeforeRCQEntity    = new DailyStatsBeforeRCQEntity($this->parsedBody, $this->logger);

    $this->dailyStatsBeforeRCQDBService->update($dailyStatsBeforeRCQEntity, $ulId);

    return $this->response;
  }
}
