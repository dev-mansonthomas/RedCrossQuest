<?php




namespace RedCrossQuest\routes\routesActions\settings;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\DailyStatsBeforeRCQDBService;
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
   * @var UniteLocaleSettingsDBService  $uniteLocaleSettingsDBService
   */
  private $uniteLocaleSettingsDBService;

  /**
   * @var UniteLocaleDBService          $uniteLocaleDBService
   */
  private $uniteLocaleDBService;

  /**
   * @var UserDBService                 $userDBService
   */
  private $userDBService;

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
   * @param UniteLocaleSettingsDBService $uniteLocaleSettingsDBService
   * @param UniteLocaleDBService $uniteLocaleDBService
   * @param UserDBService $userDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              UniteLocaleSettingsDBService  $uniteLocaleSettingsDBService,
                              UniteLocaleDBService          $uniteLocaleDBService,
                              UserDBService                 $userDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleSettingsDBService = $uniteLocaleSettingsDBService;
    $this->uniteLocaleDBService         = $uniteLocaleDBService;
    $this->userDBService                = $userDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));
    
    $ulId   = $this->decodedToken->getUlId  ();
    $roleId = $this->decodedToken->getRoleId();
    $userId = $this->decodedToken->getUid   ();

    $guiSettings =[];
    $guiSettings['mapKey'        ] = $this->settings['appSettings']['gmapAPIKey'     ];
    $guiSettings['RGPDVideo'     ] = $this->settings['appSettings']['RGPDVideo'      ];
    $guiSettings['RedQuestDomain'] = $this->settings['appSettings']['RedQuestDomain' ];

    $guiSettings['ul'            ] = $this->uniteLocaleDBService         ->getUniteLocaleById   ($ulId);
    $guiSettings['ul_settings'   ] = $this->uniteLocaleSettingsDBService ->getUniteLocaleById   ($ulId);
    $guiSettings['user'          ] = $this->userDBService                ->getUserInfoWithUserId($userId, $ulId, $roleId);
    $guiSettings['RCQVersion'    ] = $this->RCQVersion;
    $guiSettings['FirstDay'      ] = DailyStatsBeforeRCQDBService::getCurrentQueteStartDate();

    $this->response->getBody()->write(json_encode($guiSettings));

    return $this->response;
  }
}
