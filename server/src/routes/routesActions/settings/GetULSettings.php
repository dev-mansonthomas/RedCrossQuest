<?php
namespace RedCrossQuest\routes\routesActions\settings;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\ULPreferencesFirestoreDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;

class GetULSettings extends Action
{
  /**
   * @var ULPreferencesFirestoreDBService  $ULPreferencesFirestoreDBService
   */
  private $ULPreferencesFirestoreDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ULPreferencesFirestoreDBService $ULPreferencesFirestoreDBService
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
    $ulId        = $this->decodedToken->getUlId();
    $ul_settings = $this->ULPreferencesFirestoreDBService ->getULPrefs($ulId);

    if($ul_settings)
    {
      unset($ul_settings->FIRESTORE_DOC_ID);
    }

    $this->response->getBody()->write(json_encode($ul_settings));

    return $this->response;
  }
}
