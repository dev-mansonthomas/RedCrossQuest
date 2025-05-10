<?php




namespace RedCrossQuest\routes\routesActions\troncs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\Entity\TroncEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class InsertTronc extends Action
{
  /**
   * @var TroncDBService          $troncDBService
   */
  private $troncDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncDBService          $troncDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              TroncDBService          $troncDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncDBService = $troncDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId             = $this->decodedToken->getUlId();
    $troncEntity      = new TroncEntity($this->parsedBody, $this->logger);
    // It seems that sometime Gulp proxy duplicate requests, this avoid an error in such case
    if ($troncEntity->nombreTronc <= 0) {
      $this->logger->warning("Ignoring POST with nombreTronc <= 0", [
        'notes' => $troncEntity->notes
      ]);
      return $this->response->withStatus(202); // Accepted but ignored
    }

    $this->troncDBService->insert($troncEntity, $ulId);
    return $this->response;
  }
}
