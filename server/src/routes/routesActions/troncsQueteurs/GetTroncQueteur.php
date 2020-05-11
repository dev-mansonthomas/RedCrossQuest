<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\TroncQueteurBusinessService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class GetTroncQueteur extends Action
{

  /**
   * @var TroncQueteurBusinessService $troncQueteurBusinessService
   */
  private $troncQueteurBusinessService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncQueteurBusinessService $troncQueteurBusinessService
   */
  public function __construct(LoggerInterface             $logger,
                              ClientInputValidator        $clientInputValidator,
                              TroncQueteurBusinessService $troncQueteurBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurBusinessService = $troncQueteurBusinessService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('id', $this->args['id'], 1000000, true)
      ]);

    $troncQueteurId           = $this->validatedData["id"];

    $ulId      = $this->decodedToken->getUlId       ();
    $roleId    = $this->decodedToken->getRoleId     ();

    $troncQueteur   = $this->troncQueteurBusinessService->getTroncQueteurFromTroncQueteurId($troncQueteurId, $ulId, $roleId);

    $this->response->getBody()->write(json_encode($troncQueteur));
    return $this->response;
  }
}
