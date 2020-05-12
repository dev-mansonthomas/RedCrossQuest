<?php




namespace RedCrossQuest\routes\routesActions\settings;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\ULPreferencesFirestoreDBService;
use RedCrossQuest\Entity\ULPreferencesEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class UpdateRedQuestSettings extends Action
{

  /**
   * @var ULPreferencesFirestoreDBService  $ULPreferencesFirestoreDBService
   */
  private $ULPreferencesFirestoreDBService;



  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ULPreferencesFirestoreDBService  $ULPreferencesFirestoreDBService
   */
  public function __construct(LoggerInterface                 $logger,
                              ClientInputValidator            $clientInputValidator,
                              ULPreferencesFirestoreDBService $ULPreferencesFirestoreDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->ULPreferencesFirestoreDBService = $ULPreferencesFirestoreDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->logger->debug("parsed body", [$this->parsedBody]);
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withBoolean("rq_autonomous_depart_and_return" , $this->parsedBody, true, false),
        ClientInputValidatorSpecs::withBoolean("rq_display_daily_stats"          , $this->parsedBody, true, false),
        ClientInputValidatorSpecs::withString ("rq_display_queteur_ranking"      , $this->parsedBody, 8,      true),
      ]);

    $ulId   = $this->decodedToken->getUlId();

    //récupère les settings existants
    $ulPreferenceEntity = $this->ULPreferencesFirestoreDBService ->getULPrefs($ulId);

    if(!$ulPreferenceEntity)
    {//does not exist in firestore
      $data = [];

      $data['rq_autonomous_depart_and_return'] = $this->validatedData["rq_autonomous_depart_and_return"];
      $data['rq_display_daily_stats'         ] = $this->validatedData["rq_display_daily_stats"];
      $data['rq_display_queteur_ranking'     ] = $this->validatedData["rq_display_queteur_ranking"];
      $data['ul_id'                          ] = $ulId;

      $ulPreferenceEntity = ULPreferencesEntity::withArray($data, $this->logger);
    }
    else
    {
      $ulPreferenceEntity->rq_autonomous_depart_and_return = $this->validatedData["rq_autonomous_depart_and_return"];
      $ulPreferenceEntity->rq_display_daily_stats          = $this->validatedData["rq_display_daily_stats"];
      $ulPreferenceEntity->rq_display_queteur_ranking      = $this->validatedData["rq_display_queteur_ranking"];
    }

    $this->ULPreferencesFirestoreDBService ->updateUlPrefs($ulId, $ulPreferenceEntity);
    return $this->response;
  }
}
