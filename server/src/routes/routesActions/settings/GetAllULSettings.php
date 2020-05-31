<?php




namespace RedCrossQuest\routes\routesActions\settings;


use DI\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\DBService\ULPreferencesFirestoreDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UniteLocaleSettingsDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class GetAllULSettings extends Action
{
  /**
   * @var ULPreferencesFirestoreDBService $uniteLocalePrefsFirestoreService,
   */
  private $uniteLocalePrefsFirestoreService;

  /**
   * @var UniteLocaleDBService          $uniteLocaleDBService
   */
  private $uniteLocaleDBService;

  /**
   * @var UserDBService                 $userDBService
   */
  private $userDBService;

  /**
   * @var DailyStatsBeforeRCQDBService                 $dailyStatsBeforeRCQDBService
   */
  private $dailyStatsBeforeRCQDBService;

  /**
   * UniteLocaleSettingsDBService    $uniteLocaleSettingsDBService
   */
  private $uniteLocaleSettingsDBService;


  /**
   * @Inject("settings")
   * @var array $settings
   */
  protected $settings;

  /**
   * @Inject("RCQVersion")
   * @var string $RCQVersion
   */
  protected $RCQVersion;

  /**
   * @Inject("googleMapsApiKey")
   * @var string $googleMapsApiKey
   */
  private $googleMapsApiKey;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ULPreferencesFirestoreDBService $uniteLocalePrefsFirestoreService
   * @param UniteLocaleDBService $uniteLocaleDBService
   * @param UserDBService $userDBService
   * @param DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService
   * @param UniteLocaleSettingsDBService $uniteLocaleSettingsDBService
   */
  public function __construct(LoggerInterface                 $logger,
                              ClientInputValidator            $clientInputValidator,
                              ULPreferencesFirestoreDBService $uniteLocalePrefsFirestoreService,
                              UniteLocaleDBService            $uniteLocaleDBService,
                              UserDBService                   $userDBService,
                              DailyStatsBeforeRCQDBService    $dailyStatsBeforeRCQDBService,
                              UniteLocaleSettingsDBService    $uniteLocaleSettingsDBService)
  {
    parent::__construct($logger, $clientInputValidator);

    $this->uniteLocalePrefsFirestoreService = $uniteLocalePrefsFirestoreService;
    $this->uniteLocaleDBService             = $uniteLocaleDBService;
    $this->userDBService                    = $userDBService;
    $this->dailyStatsBeforeRCQDBService     = $dailyStatsBeforeRCQDBService;
    $this->uniteLocaleSettingsDBService    = $uniteLocaleSettingsDBService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId  ();
    $roleId = $this->decodedToken->getRoleId();
    $userId = $this->decodedToken->getUid   ();

    $guiSettings = new GetAllULSettingsResponse(
      $roleId == 1 ? "" : $this->googleMapsApiKey,
      $this->settings['appSettings']['RGPDVideo'      ],
      $roleId == 1 ? "" : $this->settings['appSettings']['RedQuestDomain' ],
      $this->RCQVersion,
      $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate()
    );

    if($roleId>1)
    {
      $guiSettings->ul          = $this->uniteLocaleDBService             ->getUniteLocaleById   ($ulId);
      $guiSettings->ul_settings = $this->uniteLocalePrefsFirestoreService ->getULPrefs           ($ulId);

      $ulTokens    = $this->uniteLocaleSettingsDBService->getUniteLocaleById($ulId);

      $guiSettings->ul_settings->token_benevole    = $ulTokens->token_benevole;
      $guiSettings->ul_settings->token_benevole_1j = $ulTokens->token_benevole_1j;

      if(isset($guiSettings->ul_settings))
        unset($guiSettings->ul_settings->FIRESTORE_DOC_ID);
    }
    $guiSettings->user        = $this->userDBService->getUserInfoWithUserId($userId, $ulId, $roleId);

    $this->response->getBody()->write(json_encode($guiSettings));

    return $this->response;
  }
}
