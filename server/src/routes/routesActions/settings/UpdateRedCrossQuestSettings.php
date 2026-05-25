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


class UpdateRedCrossQuestSettings extends Action
{

  /**
   * @var ULPreferencesFirestoreDBService  $ULPreferencesFirestoreDBService
   */
  private ULPreferencesFirestoreDBService $ULPreferencesFirestoreDBService;



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
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withBoolean("use_bank_bag"                , $this->parsedBody, true, false),
        ClientInputValidatorSpecs::withBoolean("check_dates_not_in_the_past" , $this->parsedBody, true, true ),
        ClientInputValidatorSpecs::withInteger("coin_order"                  , $this->parsedBody, 2  , false, ULPreferencesEntity::$COIN_ORDER_BY_SIZE),
      ]);

    //coin_order is a UL-wide preference (1 = by size [default], 2 = by value),
    //replacing the legacy per-browser $localStorage.guiSettings.coins_order on
    //the tronc_queteur counting form. Normalize anything outside {1,2} to the default.
    $coinOrder = $this->validatedData["coin_order"];
    if($coinOrder !== ULPreferencesEntity::$COIN_ORDER_BY_SIZE && $coinOrder !== ULPreferencesEntity::$COIN_ORDER_BY_VALUE)
    {
      $coinOrder = ULPreferencesEntity::$COIN_ORDER_BY_SIZE;
    }

    $ulId   = $this->decodedToken->getUlId();

    //récupère les settings existants
    $ulPreferenceEntity = $this->ULPreferencesFirestoreDBService ->getULPrefs($ulId);

    if(!$ulPreferenceEntity)
    {//does not exist in firestore
      $data = [];

      $data['use_bank_bag'               ] = $this->validatedData["use_bank_bag"];
      $data['check_dates_not_in_the_past'] = $this->validatedData["check_dates_not_in_the_past"];
      $data['coin_order'                 ] = $coinOrder;

      $data['ul_id'       ] = $ulId;

      $ulPreferenceEntity = ULPreferencesEntity::withArray($data, $this->logger);
    }
    else
    {
      $ulPreferenceEntity->use_bank_bag                = $this->validatedData["use_bank_bag"               ];
      $ulPreferenceEntity->check_dates_not_in_the_past = $this->validatedData["check_dates_not_in_the_past"];
      $ulPreferenceEntity->coin_order                  = $coinOrder;
    }

    $this->ULPreferencesFirestoreDBService ->updateUlPrefs($ulId, $ulPreferenceEntity);
    return $this->response;
  }
}
