<?php




namespace RedCrossQuest\routes\routesActions\pointsQuetes;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class GetPointQuete extends Action
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
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('id', $this->args, 1000000, true)
      ]);

    $id     = $this->validatedData["id"];
    $ulId   = $this->decodedToken->getUlId  ();
    $roleId = $this->decodedToken->getRoleId();

    $pointQuete = $this->pointQueteDBService->getPointQueteById($id, $ulId, $roleId);
    $this->response->getBody()->write(json_encode($pointQuete));

    return $this->response;
  }
}
