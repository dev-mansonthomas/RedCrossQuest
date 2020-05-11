<?php
namespace RedCrossQuest\routes\routesActions\settings;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\SettingsBusinessService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;

class GetULSetupStatus extends Action
{
  /**
   * @var SettingsBusinessService  $settingsBusinessService
   */
  private $settingsBusinessService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param SettingsBusinessService  $settingsBusinessService
   */
  public function __construct(LoggerInterface          $logger,
                              ClientInputValidator     $clientInputValidator,
                              SettingsBusinessService  $settingsBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->settingsBusinessService = $settingsBusinessService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId    = $this->decodedToken->getUlId();

    $this->response->getBody()->write(json_encode($this->settingsBusinessService->getSetupStatus($ulId)));

    return $this->response;
  }
}
