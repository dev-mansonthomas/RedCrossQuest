<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Carbon\Carbon;
use DI\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\DBService\ULPreferencesFirestoreDBService;
use RedCrossQuest\Entity\TroncQueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\PubSubService;
use RedCrossQuest\Service\RedCallService;
use Throwable;


class SaveCoinsOnTroncQueteur extends Action
{
  /**
   * @var TroncQueteurDBService          $troncQueteurDBService
   */
  private $troncQueteurDBService;

  /**
   * @var ULPreferencesFirestoreDBService $ULPreferencesFirestoreDBService
   */
  private $ULPreferencesFirestoreDBService;

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
   * @param LoggerInterface                 $logger
   * @param ClientInputValidator            $clientInputValidator
   * @param TroncQueteurDBService           $troncQueteurDBService
   * @param ULPreferencesFirestoreDBService $ULPreferencesFirestoreDBService
   * @param PubSubService                   $pubSubService
   */
  public function __construct(LoggerInterface                 $logger,
                              ClientInputValidator            $clientInputValidator,
                              TroncQueteurDBService           $troncQueteurDBService,
                              ULPreferencesFirestoreDBService $ULPreferencesFirestoreDBService,
                              PubSubService                   $pubSubService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService           = $troncQueteurDBService;
    $this->ULPreferencesFirestoreDBService = $ULPreferencesFirestoreDBService;
    $this->pubSubService                   = $pubSubService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    try
    {
      $this->validateSentData(
        [
          ClientInputValidatorSpecs::withBoolean("adminMode", $this->queryParams, false, false)
        ]);

      //if admin mode: the comptage date is not updated
      $adminMode           = $this->validatedData["adminMode"];

      $ulId      = $this->decodedToken->getUlId       ();
      $userId    = $this->decodedToken->getUid        ();
      $roleId    = $this->decodedToken->getRoleId     ();

      /** @var TroncQueteurEntity */
      $tq = new TroncQueteurEntity($this->parsedBody, $this->logger);

      if(!$adminMode)//money bag check are not performed in AdminMode
      {
        //get the setting for MoneyBags, if they are mandatory or not.
        $ulPrefs = $this->ULPreferencesFirestoreDBService->getULPrefs($ulId);

        if($ulPrefs->use_bank_bag && ($tq->coins_money_bag_id == null ||  $tq->bills_money_bag_id == null))
        {
          $this->logger->error("Les sacs de banques sont obligatoires et au moins un est null.", ["ULPrefs"=>$ulPrefs, "TroncQueteur"=>$tq]);
          $response400 = $this->response->withStatus(400);
          $response400->getBody()->write(json_encode([
            "error"         => "Les sacs de banques sont obligatoires et au moins un est null. Essayer de re-saisir les sacs de banque et sauvegarder",
            "use_bank_bag"  => $ulPrefs->use_bank_bag,
            "TroncQueteur"  => $tq
          ]));
          return $response400;
        }
      }

      $this->troncQueteurDBService->updateCoinsCount($tq, $adminMode, $ulId, $userId);

    }
    catch(Throwable $exception)
    {
      $this->logger->error("Error while updateCoinsCount",["tq"=>$tq]);
      return $this->response->withStatus(500, "Error while updating TroncQueteur as admin") ;
    }

    try
    {
      $tqUpdated = $this->troncQueteurDBService->getTroncQueteurById($tq->id, $ulId,$roleId);
      var_dump($tqUpdated);
      
      $tqUpdated->preparePubSubPublishing();
      if($adminMode)
      {
        $tqUpdated->saveAsAdmin=1;
      }

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
      $this->logger->error("error while publishing SaveCoinsOnTroncQueteur",
        array("messageProperties" => $messageProperties,
          "troncQueteurEntity"    => $tqUpdated,
          Logger::$EXCEPTION => $exception));
      //do not rethrow
    }

    return $this->response;
  }
}
