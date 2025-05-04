<?php




namespace RedCrossQuest\routes\routesActions\namedDonations;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\NamedDonationDBService;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class ListNamedDonations extends Action
{
  /**
   * @var NamedDonationDBService        $namedDonationDBService
   */
  private NamedDonationDBService $namedDonationDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param NamedDonationDBService        $namedDonationDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              NamedDonationDBService        $namedDonationDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->namedDonationDBService = $namedDonationDBService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId();
    $roleId = $this->decodedToken->getRoleId();


    $validations = [
      ClientInputValidatorSpecs::withInteger('pageNumber' , $this->queryParams, 100  , false    ),
      ClientInputValidatorSpecs::withInteger('rowsPerPage', $this->queryParams, 100  , false    ),
      ClientInputValidatorSpecs::withString ("q"          , $this->queryParams, 100 , false    ),
      ClientInputValidatorSpecs::withBoolean("deleted"    , $this->queryParams, false  , false),
      ClientInputValidatorSpecs::withInteger("year"       , $this->queryParams, 2050 , false    ),
      ClientInputValidatorSpecs::withInteger('admin_ul_id', $this->queryParams, 1000 ,false     )
    ];

    $this->validateSentData($validations);

    $admin_ul_id  = $this->validatedData["admin_ul_id"];

    if($admin_ul_id != null && $roleId == 9)
    {
      $this->validatedData['ul_id'] = $this->validatedData["admin_ul_id"];
    }
    else
    {
      $this->validatedData['ul_id'] = $ulId;
    }

    //$this->logger->info("searching named donation", array('q'=>$query, 'deleted'=>$deleted, 'year'=>$year, 'admin_ul_id'=>$adminUlId));

    $pageableRequest = new PageableRequestEntity($this->validatedData);

    $namedDonations = $this->namedDonationDBService->getNamedDonations($pageableRequest);

    $this->response->getBody()->write(json_encode($namedDonations));

    return $this->response;
  }
}
