<?php




namespace RedCrossQuest\routes\routesActions\unitesLocales;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class ListUniteLocaleRegistration extends Action
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
    $this->validateSentData([
      ClientInputValidatorSpecs::withInteger('pageNumber'         , $this->queryParams, 100 , false    ),
      ClientInputValidatorSpecs::withInteger('rowsPerPage'        , $this->queryParams, 100 , false    ),
      ClientInputValidatorSpecs::withInteger("registration_status", $this->queryParams, 2   , false, 0)
    ]);

    $pageableRequest = new PageableRequestEntity($this->validatedData);

    $uls = $this->uniteLocaleDBService->listULRegistrations($pageableRequest);

    $this->response->getBody()->write(json_encode($uls));

    return $this->response;
  }
}
