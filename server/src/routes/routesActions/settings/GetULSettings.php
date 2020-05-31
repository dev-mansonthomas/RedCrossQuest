<?php
namespace RedCrossQuest\routes\routesActions\settings;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\ULPreferencesFirestoreDBService;
use RedCrossQuest\DBService\UniteLocaleSettingsDBService;
use RedCrossQuest\Entity\ULPreferencesEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;

class GetULSettings extends Action
{
  /**
   * @var ULPreferencesFirestoreDBService  $ULPreferencesFirestoreDBService
   */
  private $ULPreferencesFirestoreDBService;

  /**
   * UniteLocaleSettingsDBService    $uniteLocaleSettingsDBService
   */
  private $uniteLocaleSettingsDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ULPreferencesFirestoreDBService $ULPreferencesFirestoreDBService
   * @param UniteLocaleSettingsDBService    $uniteLocaleSettingsDBService
   */
  public function __construct(LoggerInterface                 $logger,
                              ClientInputValidator            $clientInputValidator,
                              ULPreferencesFirestoreDBService $ULPreferencesFirestoreDBService,
                              UniteLocaleSettingsDBService    $uniteLocaleSettingsDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->ULPreferencesFirestoreDBService = $ULPreferencesFirestoreDBService;
    $this->uniteLocaleSettingsDBService    = $uniteLocaleSettingsDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId        = $this->decodedToken->getUlId();
    $ul_settings = $this->ULPreferencesFirestoreDBService ->getULPrefs($ulId);
    $ulTokens    = $this->uniteLocaleSettingsDBService->getUniteLocaleById($ulId);

    $ul_settings->token_benevole    = $ulTokens->token_benevole;
    $ul_settings->token_benevole_1j = $ulTokens->token_benevole_1j;

    if($ul_settings)
    {
      unset($ul_settings->FIRESTORE_DOC_ID);
    }

    $this->response->getBody()->write(json_encode($ul_settings));

    return $this->response;
  }
}
