<?php




namespace RedCrossQuest\routes\routesActions\settings;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
use RedCrossQuest\DBService\ULPreferencesFirestoreDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UniteLocaleSettingsDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


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
   * @Inject("settings")
   * @var array settings
   */
  protected $settings;

  /**
   * @Inject("RCQVersion")
   * @var string RCQVersion
   */
  protected $RCQVersion;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ULPreferencesFirestoreDBService $uniteLocalePrefsFirestoreService
   * @param UniteLocaleDBService $uniteLocaleDBService
   * @param UserDBService $userDBService
   * @param DailyStatsBeforeRCQDBService $dailyStatsBeforeRCQDBService
   */
  public function __construct(LoggerInterface                 $logger,
                              ClientInputValidator            $clientInputValidator,
                              ULPreferencesFirestoreDBService $uniteLocalePrefsFirestoreService,
                              UniteLocaleDBService            $uniteLocaleDBService,
                              UserDBService                   $userDBService,
                              DailyStatsBeforeRCQDBService    $dailyStatsBeforeRCQDBService)
  {
    parent::__construct($logger, $clientInputValidator);

    $this->uniteLocalePrefsFirestoreService = $uniteLocalePrefsFirestoreService;
    $this->uniteLocaleDBService             = $uniteLocaleDBService;
    $this->userDBService                    = $userDBService;
    $this->dailyStatsBeforeRCQDBService     = $dailyStatsBeforeRCQDBService;
  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId  ();
    $roleId = $this->decodedToken->getRoleId();
    $userId = $this->decodedToken->getUid   ();

    $guiSettings = new GetAllULSettingsResponse(
      $roleId == 1 ? "" : $this->settings['appSettings']['gmapAPIKey'     ],
      $this->settings['appSettings']['RGPDVideo'      ],
      $roleId == 1 ? "" : $this->settings['appSettings']['RedQuestDomain' ],
      $this->RCQVersion,
      $this->dailyStatsBeforeRCQDBService->getCurrentQueteStartDate()
    );

    $guiSettings->ul          = $roleId == 1 ? "" : $this->uniteLocaleDBService             ->getUniteLocaleById   ($ulId);
    $guiSettings->ul_settings = $roleId == 1 ? "" : $this->uniteLocalePrefsFirestoreService ->getULPrefs           ($ulId);
    if(isset($guiSettings->ul_settings))
      unset($guiSettings->ul_settings->FIRESTORE_DOC_ID);
    $guiSettings->user        = $this->userDBService->getUserInfoWithUserId($userId, $ulId, $roleId);

    $this->response->getBody()->write(json_encode($guiSettings));

    return $this->response;
  }
}
