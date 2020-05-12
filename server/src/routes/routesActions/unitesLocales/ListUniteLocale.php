<?php




namespace RedCrossQuest\routes\routesActions\unitesLocales;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class ListUniteLocale extends Action
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
    $roleId = $this->decodedToken->getRoleId();

    if(array_key_exists('q', $this->queryParams) && $roleId == 9)
    {
      $this->validateSentData(
        [
          ClientInputValidatorSpecs::withString("q", $this->queryParams, 50 , true)
        ]);

      $query  = $this->validatedData["q"];
      $uls    = $this->uniteLocaleDBService->searchUniteLocale($query);
      $this->response->getBody()->write(json_encode($uls));
    }
    else
    {
      $this->response->getBody()->write(json_encode([]));
    }

    return $this->response;
  }
}
