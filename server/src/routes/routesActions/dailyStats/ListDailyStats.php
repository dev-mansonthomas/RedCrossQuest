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


class ListDailyStats extends Action
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
    $ulId     = $this->decodedToken->getUlId();

    $this->validateSentData([
      ClientInputValidatorSpecs::withInteger('year', $this->getParam('year'), 2050, false)
    ]);

    $year  = $this->validatedData["year"];

    if( $year == null)
    {
      $year =  date("Y");
    }

    //$this->get(LoggerInterface::class)->info("DailyStats list - UL ID '".$ulId."'' role ID : $roleId");
    $dailyStats = $this->dailyStatsBeforeRCQDBService->getDailyStats($ulId, $year);

/*    if($dailyStats !== null && count($dailyStats) > 0)
    {
      $this->logger->info($dailyStats[0]->generateCSVHeader());
      $this->logger->info($dailyStats[0]->generateCSVRow   ());

    }*/
    $this->response->getBody()->write(json_encode($dailyStats));

    return $this->response;
  }
}
