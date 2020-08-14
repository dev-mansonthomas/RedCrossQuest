<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\TroncQueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class CancelRetourOnTroncQueteur extends Action
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
    $ulId      = $this->decodedToken->getUlId       ();
    $userId    = $this->decodedToken->getUid        ();

    /** @var TroncQueteurEntity */
    $tq = new TroncQueteurEntity($this->parsedBody, $this->logger);

    $numberOfRowUpdated = $this->troncQueteurDBService->cancelRetour($tq, $ulId, $userId);
    if($numberOfRowUpdated != 1 )
    {
      $this->logger->error("numberOfRowUpdated!=1, likely that comptage is not null",
        array("tronc_queteur"=>$tq->id, "numberOfRowUpdated"=>$numberOfRowUpdated));
      throw new Exception("numberOfRowUpdated=$numberOfRowUpdated, likely that comptage is not null");
    }

    return $this->response;
  }
}
