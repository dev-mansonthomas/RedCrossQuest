<?php




namespace RedCrossQuest\routes\routesActions\troncQueteurHistory;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class GetTroncQueteurHistoryFromTQID extends Action
{
  /**
   * @var TroncQueteurDBService         $troncQueteurDBService
   */
  private $troncQueteurDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncQueteurDBService         $troncQueteurDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              TroncQueteurDBService         $troncQueteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService = $troncQueteurDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('tronc_queteur_id', $this->queryParams, 1000000, true)
      ]);

    $troncQueteurId  = $this->validatedData["tronc_queteur_id"];
    $ulId            = $this->decodedToken->getUlId  ();
    $roleId          = $this->decodedToken->getRoleId();

    $troncQueteurHistory = $this->troncQueteurDBService->getTroncQueteurHistoryById($troncQueteurId, $ulId, $roleId);

    $this->response->getBody()->write(json_encode($troncQueteurHistory));

    return $this->response;
  }
}
