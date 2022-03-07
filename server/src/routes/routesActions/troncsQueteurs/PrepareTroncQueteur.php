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
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\PubSubService;


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
   * @var PubSubService           $pubSubService
   */
  private $pubSubService;

  /**
   * @Inject("settings")
   * @var array settings
   */
  protected $settings;

  /**
   * @param LoggerInterface               $logger
   * @param ClientInputValidator          $clientInputValidator
   * @param TroncQueteurDBService         $troncQueteurDBService
   * @param TroncQueteurBusinessService   $troncQueteurBusinessService
   * @param PubSubService                 $pubSubService
   */
  public function __construct(LoggerInterface             $logger,
                              ClientInputValidator        $clientInputValidator,
                              TroncQueteurDBService       $troncQueteurDBService,
                              TroncQueteurBusinessService $troncQueteurBusinessService,
                              PubSubService               $pubSubService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService       = $troncQueteurDBService;
    $this->troncQueteurBusinessService = $troncQueteurBusinessService;
    $this->pubSubService               = $pubSubService;
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
    $this->logger->debug("Preparation Tronc - Depart Theorique - before hasQueteAlreadyStarted", ["dt"=>$tq->depart_theorique]);
    $hasQueteAlreadyStarted = $this->troncQueteurBusinessService->hasQueteAlreadyStarted($this->settings['appSettings']['deploymentType'], $tq->depart_theorique);
    $this->logger->debug("Preparation Tronc - Depart Theorique - After hasQueteAlreadyStarted", ["dt"=>$tq->depart_theorique]);
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

      $tq = $this->troncQueteurDBService->getTroncQueteurById($insertResponse->lastInsertId, $ulId);
      $tq->preparePubSubPublishing();
      
      $messageProperties  = [
        'ulId'          => "".$ulId,
        'uId'           => "".$userId,
        'queteurId'     => "".$tq->queteur_id,
        'troncQueteurId'=> "".$tq->id
      ];

      try
      {
        $this->pubSubService->publish(
          $this->settings['PubSub']['tronc_queteur_create_topic'],
          $tq,
          $messageProperties,
          true,
          true);
      }
      catch(Exception $exception)
      {
        $this->logger->error("error while publishing PrepareTroncQueteur",
          array("messageProperties"=> $messageProperties,
            "troncQueteurEntity"    => $tq,
            Logger::$EXCEPTION => $exception));
        //do not rethrow
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
