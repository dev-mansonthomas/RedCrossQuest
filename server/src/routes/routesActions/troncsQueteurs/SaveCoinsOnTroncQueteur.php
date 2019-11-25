<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\Entity\TroncQueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\PubSubService;


class SaveCoinsOnTroncQueteur extends Action
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
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncQueteurDBService $troncQueteurDBService
   * @param PubSubService $pubSubService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              TroncQueteurDBService   $troncQueteurDBService,
                              PubSubService           $pubSubService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService = $troncQueteurDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withBoolean("adminMode"          , $this->getParam('adminMode'          ), false, false)
      ]);

    //c'est bien le troncId qu'on passe ici, on va supprimer tout les tronc_queteur qui ont ce tronc_id et départ ou retour à nulle
    $adminMode           = $this->validatedData["adminMode"];

    $ulId      = $this->decodedToken->getUlId       ();
    $userId    = $this->decodedToken->getUid        ();

    /** @var TroncQueteurEntity */
    $tq = new TroncQueteurEntity($this->parsedBody, $this->logger);
    $this->troncQueteurDBService->updateCoinsCount($tq, $adminMode, $ulId, $userId);

    /**
     * Publish on pubsub to trigger recomputing of metrics for the queteur
     */
    $responseMessageIds = null;
    $messageProperties  = ['ul_id'=>"".$ulId,'user_id'=>"".$userId, 'queteur_id'=>"".$tq->queteur_id, 'tronc_queteur_id'=>"".$tq->id];
    $dataToPublish      = ['ul_id'=>$ulId,'user_id'=>$userId, 'queteur_id'=>$tq->queteur_id, 'tronc_queteur_id'=>$tq->id,
      'tronc_id'   => $tq->tronc_id, 'point_quete_id'=>$tq->point_quete_id, 'update_date' => Carbon::now()->toDateTimeString(),
      'first_edit' => $tq->comptage == null ];
    try
    {
      $this->pubSubService->publish(
        $this->settings['PubSub']['tronc_queteur_update_topic'],
        $dataToPublish,
        $messageProperties,
        true,
        true);
    }
    catch(\Exception $exception)
    {
      $this->logger->error("error while publishing on topic", array("messageProperties"=>$messageProperties,"exception"=>$exception));
      //do not rethrow
    }


    return $this->response;
  }
}