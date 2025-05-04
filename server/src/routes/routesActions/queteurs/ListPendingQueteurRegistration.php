<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class ListPendingQueteurRegistration extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private QueteurDBService $queteurDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService          $queteurDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService          $queteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService = $queteurDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId     = $this->decodedToken->getUlId();

    $this->validateSentData([
      ClientInputValidatorSpecs::withInteger('pageNumber'         , $this->queryParams, 100 , false    ),
      ClientInputValidatorSpecs::withInteger('rowsPerPage'        , $this->queryParams, 100 , false    ),
      ClientInputValidatorSpecs::withInteger("registration_status", $this->queryParams, 2   , false, 0)
    ]);

    $this->validatedData['ul_id'] = $ulId;

    $pageableRequest = new PageableRequestEntity($this->validatedData);

    $queteurs = $this->queteurDBService->listPendingQueteurRegistration($pageableRequest);

    $this->response->getBody()->write(json_encode($queteurs));

    return $this->response;
  }
}
