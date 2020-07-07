<?php




namespace RedCrossQuest\routes\routesActions\unitesLocales;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class GetUniteLocaleRegistration extends Action
{


  /**
   * @var UniteLocaleDBService          $uniteLocaleDBService
   */
  private $uniteLocaleDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UniteLocaleDBService $uniteLocaleDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              UniteLocaleDBService          $uniteLocaleDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleDBService         = $uniteLocaleDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('id', $this->args, 1000000, true)
      ]);

    $ulRegistrationId = $this->validatedData["id"];

    $this->response->getBody()->write(json_encode($this->uniteLocaleDBService->getULRegistration($ulRegistrationId)));

    return $this->response;
  }
}
