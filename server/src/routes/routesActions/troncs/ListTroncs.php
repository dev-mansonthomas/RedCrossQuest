<?php




namespace RedCrossQuest\routes\routesActions\troncs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class ListTroncs extends Action
{
  /**
   * @var TroncDBService          $troncDBService
   */
  private $troncDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncDBService          $troncDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              TroncDBService          $troncDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncDBService = $troncDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('pageNumber'  , $this->queryParams, 100     , false    ),
        ClientInputValidatorSpecs::withInteger('rowsPerPage' , $this->queryParams, 100     , false    ),
        ClientInputValidatorSpecs::withBoolean("active"      , $this->queryParams, false     , true),
        ClientInputValidatorSpecs::withInteger("type"        , $this->queryParams, 5       , false, null),
        ClientInputValidatorSpecs::withInteger("q"           , $this->queryParams, 1000000 , false, null),
      ]);

    $ulId   = $this->decodedToken  ->getUlId  ();

    $pageableRequest = new PageableRequestEntity($this->validatedData);

    $troncs = $this->troncDBService->getTroncs($pageableRequest, $ulId);

    $this->response->getBody()->write(json_encode($troncs));

    return $this->response;
  }
}
