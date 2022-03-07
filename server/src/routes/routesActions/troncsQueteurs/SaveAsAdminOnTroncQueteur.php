<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use DI\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\TroncQueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\PubSubService;


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
   * @Inject("settings")
   * @var array settings
   */
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

    /** @var TroncQueteurEntity */
    $tq = new TroncQueteurEntity($this->parsedBody, $this->logger);

    $this->troncQueteurDBService->updateTroncQueteurAsAdmin($tq, $ulId, $userId);

    $tqUpdated = $this->troncQueteurDBService->getTroncQueteurById($tq->id, $ulId);
    $tqUpdated->preparePubSubPublishing();
    $tqUpdated->saveAsAdmin=1;

    $messageProperties  = [
      'ulId'          => "".$ulId,
      'uId'           => "".$userId,
      'queteurId'     => "".$tqUpdated->queteur_id,
      'troncQueteurId'=> "".$tqUpdated->id
    ];

    try
    {
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
