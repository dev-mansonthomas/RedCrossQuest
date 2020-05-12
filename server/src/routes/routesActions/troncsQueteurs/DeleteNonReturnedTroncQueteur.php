<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class DeleteNonReturnedTroncQueteur extends Action
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
   * Either :  This queteur has been affected another tronc (A) that the one being scanned (B). => mark the tronc_queteur with tronc (A) one as deleted
   * otherwise : mark the tronc_queteur with the scanned tronc as deleted (so other people had a tronc_queteur prepared this tronc, and they are marked as deleted)
   * Mark as deleted occurs only on tronc_queteur that have retour or depart set to null.
   *
   *
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('tronc_id', $this->args, 1000000, true)
      ]);

    $troncId = $this->validatedData["tronc_id"];

    $ulId   = $this->decodedToken->getUlId();
    $userId = $this->decodedToken->getUid ();

    $this->logger->warning("user $userId of UL $ulId is deleting tronc id=$troncId");

    $this->troncQueteurDBService->deleteNonReturnedTroncQueteur($troncId, $ulId, $userId);

    return $this->response;
  }
}
