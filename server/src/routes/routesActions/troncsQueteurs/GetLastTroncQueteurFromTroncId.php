<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\TroncQueteurBusinessService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class GetLastTroncQueteurFromTroncId extends Action
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
   * @throws \Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('tronc_id', $this->getParam('tronc_id'), 1000000, true)
      ]);

    $tronc_id  = $this->validatedData["tronc_id"];
    $ulId      = $this->decodedToken->getUlId       ();
    $roleId    = $this->decodedToken->getRoleId     ();

    $troncQueteur = $this->troncQueteurBusinessService->getLastTroncQueteurFromTroncId($tronc_id, $ulId, $roleId);

    $this->response->getBody()->write(json_encode($troncQueteur));

    return $this->response;
  }
}
