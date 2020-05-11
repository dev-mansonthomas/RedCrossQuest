<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class GetTroncsQueteursForTroncId extends Action
{
  /**
   * @var TroncQueteurDBService          $troncQueteurDBService
   */
  private $troncQueteurDBService;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncQueteurDBService $troncQueteurDBService
   */
  public function __construct(LoggerInterface             $logger,
                              ClientInputValidator        $clientInputValidator,
                              TroncQueteurDBService       $troncQueteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService       = $troncQueteurDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('tronc_id', $this->getParam('tronc_id'), 1000000, true)
      ]);

    $tronc_id     = $this->validatedData["tronc_id"];
    $ulId         = $this->decodedToken->getUlId       ();
    $troncQueteur = $this->troncQueteurDBService->getTroncsQueteurByTroncId($tronc_id, $ulId);

    $this->response->getBody()->write(json_encode($troncQueteur));

    return $this->response;
  }
}
