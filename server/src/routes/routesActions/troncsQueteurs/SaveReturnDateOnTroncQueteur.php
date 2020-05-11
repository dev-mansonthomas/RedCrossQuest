<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\TroncQueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class SaveReturnDateOnTroncQueteur extends Action
{
  /**
   * @var TroncQueteurDBService          $troncQueteurDBService
   */
  private $troncQueteurDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncQueteurDBService          $troncQueteurDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              TroncQueteurDBService   $troncQueteurDBService)
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
        ClientInputValidatorSpecs::withBoolean("dateDepartIsMissing", $this->getParam('dateDepartIsMissing'), false, false)
      ]);

    $dateDepartIsMissing = $this->validatedData["dateDepartIsMissing"];

    $ulId      = $this->decodedToken->getUlId       ();
    $userId    = $this->decodedToken->getUid        ();

    /** @var TroncQueteurEntity */
    $tq = new TroncQueteurEntity($this->parsedBody, $this->logger);

    // When scanning a Tronc for a 'Retour' and the Depart date is missing, the user can fill the missing departure date and record the return date at the same time.
    // If so, we add a parameter dateDepartIsMissing=true to notify backend that the depart must be updated
    if($dateDepartIsMissing)
    {
      $this->logger->info("Setting date depart that was missing for tronc_queteur", array("id"=>$tq->id, "depart" => $tq->depart));
      $this->troncQueteurDBService->setDepartToCustomDate($tq, $ulId, $userId);
    }
    $this->troncQueteurDBService->updateRetour($tq, $ulId, $userId);


    return $this->response;
  }
}
