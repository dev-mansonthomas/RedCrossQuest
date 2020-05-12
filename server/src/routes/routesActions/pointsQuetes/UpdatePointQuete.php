<?php




namespace RedCrossQuest\routes\routesActions\pointsQuetes;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\Entity\PointQueteEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class UpdatePointQuete extends Action
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
    $roleId = $this->decodedToken->getRoleId();

    $pointQueteEntity    = new PointQueteEntity($this->parsedBody, $this->logger);

    $this->pointQueteDBService->update($pointQueteEntity, $ulId, $roleId);

    return $this->response;
  }
}
