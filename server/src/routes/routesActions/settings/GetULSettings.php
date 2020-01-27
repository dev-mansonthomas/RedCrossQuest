<?php




namespace RedCrossQuest\routes\routesActions\settings;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UniteLocaleSettingsDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\routes\routesActions\pointsQuetes\GetULSettingsResponse;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


class GetULSettings extends Action
{
  /**
   * @var UniteLocaleSettingsDBService  $uniteLocaleSettingsDBService
   */
  private $uniteLocaleSettingsDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UniteLocaleSettingsDBService  $uniteLocaleSettingsDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              UniteLocaleSettingsDBService  $uniteLocaleSettingsDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleSettingsDBService = $uniteLocaleSettingsDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));
    $ulId    = $this->decodedToken->getUlId();

    $guiSettings = new GetULSettingsResponse();
    $guiSettings->ul_settings = $this->uniteLocaleSettingsDBService ->getUniteLocaleById   ($ulId);

    $this->response->getBody()->write(json_encode($guiSettings));

    return $this->response;
  }
}
