<?php




namespace RedCrossQuest\routes\routesActions\uniteLocale;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


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
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));
    
    $roleId = $this->decodedToken->getRoleId();

    if(array_key_exists('q', $this->queryParams) && $roleId == 9)
    {
      $this->validateSentData(
        [
          ClientInputValidatorSpecs::withString("q", $this->getParam('q'), 50 , true)
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
