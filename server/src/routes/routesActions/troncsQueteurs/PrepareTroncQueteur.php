<?php
namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use DI\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\TroncQueteurBusinessService;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\TroncQueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class PrepareTroncQueteur extends Action
{
  /**
   * @var TroncQueteurDBService          $troncQueteurDBService
   */
  private $troncQueteurDBService;

  /**
   * @var TroncQueteurBusinessService   $troncQueteurBusinessService
   */
  private $troncQueteurBusinessService;

  /**
   * @Inject("settings")
   * @var array settings
   */
  protected $settings;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncQueteurDBService $troncQueteurDBService
   * @param TroncQueteurBusinessService   $troncQueteurBusinessService
   */
  public function __construct(LoggerInterface             $logger,
                              ClientInputValidator        $clientInputValidator,
                              TroncQueteurDBService       $troncQueteurDBService,
                              TroncQueteurBusinessService $troncQueteurBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService       = $troncQueteurDBService;
    $this->troncQueteurBusinessService = $troncQueteurBusinessService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId      = $this->decodedToken->getUlId       ();
    $userId    = $this->decodedToken->getUid        ();

    $tq    = new TroncQueteurEntity($this->parsedBody, $this->logger);
    $hasQueteAlreadyStarted = $this->troncQueteurBusinessService->hasQueteAlreadyStarted($this->settings['appSettings']['deploymentType'], $tq->depart_theorique);

    if(!$hasQueteAlreadyStarted)
    {//enforce policy :  can't prepare or depart tronc before the start of the quête
      $this->response->getBody()->write(json_encode(new PrepareTroncQueteurQueteNotStartedResponse()));
      return $this->response;
    }

    //throw new Exception("test exception");
    $insertResponse = $this->troncQueteurDBService->insert($tq, $ulId, $userId);

    if($insertResponse->troncInUse != true)
    {//if the tronc is not already in use, the we can save the preparation

      if($tq->preparationAndDepart == true)
      {//if the user click to Save And perform the depart, we proceed and save the depart
        $this->troncQueteurDBService->setDepartToNow($insertResponse->lastInsertId, $ulId, $userId);
      }
    }
    else
    {
      $this->logger->warning("TroncQueteur POST PREPARATION - TRONC IN USE", ["troncQueteur"=>$tq, "insertResponse"=>$insertResponse ]);
    }

    //in any case, we return the insert response
    $this->response->getBody()->write(json_encode($insertResponse));
    return $this->response;
  }
}
