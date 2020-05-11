<?php




namespace RedCrossQuest\routes\routesActions\pointsQuetes;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class ListPointsQuetes extends Action
{
  /**
   * @var PointQueteDBService     $pointQueteDBService
   */
  private $pointQueteDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param PointQueteDBService     $pointQueteDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              PointQueteDBService     $pointQueteDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->pointQueteDBService = $pointQueteDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId  ();

    $pointQuetes = $this->pointQueteDBService->getPointQuetes($ulId);
    $this->response->getBody()->write(json_encode($pointQuetes));

    return $this->response;
  }
}
