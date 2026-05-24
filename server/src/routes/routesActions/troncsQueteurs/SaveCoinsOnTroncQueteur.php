<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Carbon\Carbon;
use DI\Attribute\Inject;
use Exception;
use InvalidArgumentException;
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
   * @var array settings
   */
  #[Inject("settings")]
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
    catch(InvalidArgumentException $iae)
    {
      //CB payload validation failure thrown by CreditCardDBService::dedupAndValidateCBEntities
      //(same amount with different quantities). The frontend should have caught this case via
      //hasCBDetailsForDuplicateAmount(), so reaching this branch indicates a stale client, a
      //replayed request, or a direct API call. Returning 400 with details lets the client
      //surface the message; logged as ERROR with payload context for debugging.
      $this->logger->error("CB payload validation failed on SaveCoinsOnTroncQueteur",
        ["tq"=>$tq, "ulId"=>$ulId, "userId"=>$userId, Logger::$EXCEPTION => $iae]);
      $response400 = $this->response->withStatus(400);
      $response400->getBody()->write(json_encode([
        "error"          => $iae->getMessage(),
        "errorType"      => "cb_payload_validation",
        "troncQueteurId" => $tq->id,
      ], JSON_UNESCAPED_UNICODE));
      return $response400;
    }
    catch(Throwable $exception)
    {
      $this->logger->error("Error while updateCoinsCount",["tq"=>$tq, Logger::$EXCEPTION => $exception]);
      return $this->response->withStatus(500, "Error while updating TroncQueteur as admin") ;
    }

    //PubSub publishing disabled — see docs/todo.md "PubSub cleanup".
    //Topic `tronc_queteur_update` consumer (Google Spreadsheet feed) abandoned.
    /*
    try
    {
      $tqUpdated = $this->troncQueteurDBService->getTroncQueteurById($tq->id, $ulId,$roleId);
      //var_dump($tqUpdated);

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
    */

    return $this->response;
  }
}
