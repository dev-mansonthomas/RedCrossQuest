<?php




namespace RedCrossQuest\routes\routesActions\troncs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class GetTronc extends Action
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
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('id', $this->args, 1000000, true)
      ]);

    $ulId    = $this->decodedToken->getUlId();
    $troncId = $this->validatedData["id"];
    $roleId  = $this->decodedToken->getRoleId     ();

    $tronc = $this->troncDBService->getTroncById($troncId, $ulId, $roleId);
    if($tronc != null)
    {
      $this->response->getBody()->write(json_encode($tronc));
    }
    else
    {
      $response404 = $this->response->withStatus(404);
      $response404->getBody()->write(json_encode(["error"=>'tronc not found']));
      return $response404;
    }


    return $this->response;
  }
}
