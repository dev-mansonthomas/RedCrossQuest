<?php




namespace RedCrossQuest\routes\routesActions\pointsQuetes;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\PointQueteDBService;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class SearchPointsQuetes extends Action
{
  /**
   * @var PointQueteDBService     $pointQueteDBService
   */
  private PointQueteDBService $pointQueteDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param PointQueteDBService     $pointQueteDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              PointQueteDBService     $pointQueteDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->pointQueteDBService = $pointQueteDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('pageNumber'      , $this->queryParams, 100  , false    ),
        ClientInputValidatorSpecs::withInteger('rowsPerPage'     , $this->queryParams, 100  , false    ),
        ClientInputValidatorSpecs::withString ("q"               , $this->queryParams, 40  , false    ),
        ClientInputValidatorSpecs::withInteger("point_quete_type", $this->queryParams, 10   , false    ),
        ClientInputValidatorSpecs::withBoolean("active"          , $this->queryParams, false  , true ),
        ClientInputValidatorSpecs::withInteger('admin_ul_id'     , $this->queryParams, 1000 , false    )
      ]);

    $admin_ul_id      = $this->validatedData["admin_ul_id"];
    $ulId             = $this->decodedToken->getUlId   ();
    $roleId           = $this->decodedToken->getRoleId ();

    if($admin_ul_id != null && $roleId == 9)
    {
      $this->validatedData['ul_id'] = $this->validatedData["admin_ul_id"];
    }
    else
    {
      $this->validatedData['ul_id'] = $ulId;
    }

    $pageableRequest = new PageableRequestEntity($this->validatedData);

    $pointQuetes = $this->pointQueteDBService->searchPointQuetes($pageableRequest);

    $this->response->getBody()->write(json_encode($pointQuetes));

    return $this->response;
  }
}
