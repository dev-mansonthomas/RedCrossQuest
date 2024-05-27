<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use DI\Attribute\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\TroncQueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\PubSubService;
use Throwable;


class SaveAsAdminOnTroncQueteur extends Action
{
  /**
   * @var TroncQueteurDBService          $troncQueteurDBService
   */
  private $troncQueteurDBService;

  /**
   * @var PubSubService           $pubSubService
   */
  private $pubSubService;

  /**
   * @var array settings
   */
  #[Inject("settings")]
  protected $settings;

  /**
   * @param LoggerInterface       $logger
   * @param ClientInputValidator  $clientInputValidator
   * @param TroncQueteurDBService $troncQueteurDBService
   * @param PubSubService         $pubSubService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              TroncQueteurDBService   $troncQueteurDBService,
                              PubSubService           $pubSubService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService = $troncQueteurDBService;
    $this->pubSubService         = $pubSubService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId      = $this->decodedToken->getUlId       ();
    $userId    = $this->decodedToken->getUid        ();
    $roleId    = $this->decodedToken->getRoleId     ();

    try
    {
      /** @var TroncQueteurEntity */
      $tq = new TroncQueteurEntity($this->parsedBody, $this->logger);

      $this->troncQueteurDBService->updateTroncQueteurAsAdmin($tq, $ulId, $userId);
    }
    catch(Throwable $exception)
    {
      $this->logger->error("Error while updateTroncQueteurAsAdmin",["tq"=>$tq]);
      return $this->response->withStatus(500, "Error while updating TroncQueteur as admin") ;
    }
    
    try
    {
      $tqUpdated = $this->troncQueteurDBService->getTroncQueteurById($tq->id, $ulId,$roleId);
      $tqUpdated->preparePubSubPublishing();
      $tqUpdated->saveAsAdmin=1;

      $messageProperties  = [
        'ulId'          => "".$ulId,
        'uId'           => "".$userId,
        'queteurId'     => "".$tqUpdated->queteur_id,
        'troncQueteurId'=> "".$tqUpdated->id
      ];

      $this->pubSubService->publish(
        $this->settings['PubSub']['tronc_queteur_update_topic'],
        $tqUpdated,
        $messageProperties,
        true,
        true);
    }
    catch(Exception $exception)
    {
      $this->logger->error("error while publishing SaveAsAdminOnTroncQueteur",
        array("messageProperties" => $messageProperties,
          "troncQueteurEntity"    => $tqUpdated,
          Logger::$EXCEPTION => $exception));
      //do not rethrow
    }

    return $this->response;
  }
}
