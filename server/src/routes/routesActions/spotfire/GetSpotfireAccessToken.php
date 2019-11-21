<?php




namespace RedCrossQuest\routes\routesActions\spotfire;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\SpotfireAccessDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


class GetSpotfireAccessToken extends Action
{
  /**
   * @var SpotfireAccessDBService       $spotfireAccessDBService
   */
  private $spotfireAccessDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param SpotfireAccessDBService $spotfireAccessDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              SpotfireAccessDBService       $spotfireAccessDBService)
  {
    parent::__construct($logger, $clientInputValidator);

    $this->spotfireAccessDBService        = $spotfireAccessDBService;
  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $ulId           = $this->decodedToken->getUlId  ();
    $userId         = $this->decodedToken->getUid();


    $validToken = $this->spotfireAccessDBService->getValidToken($userId, $ulId);

    $this->response->getBody()->write(json_encode($validToken));

    return $this->response;
  }
}
