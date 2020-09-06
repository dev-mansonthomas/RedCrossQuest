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


class ListQueteurs extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService $queteurDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService        $queteurDBService
                              )
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
    $roleId   = $this->decodedToken->getRoleId();
    $ulId     = $this->decodedToken->getUlId  ();

    $anonymization_token = $this->getParam('anonymization_token');

    if($anonymization_token != null && strlen($anonymization_token) > 0 && $roleId >= 4)
    {// If the token is given, then other search criteria are ignored. RGPD anonymised volunteer
      $this->validateSentData([ClientInputValidatorSpecs::withString("anonymization_token", $this->queryParams, 36 , true, ClientInputValidator::$UUID_VALIDATION)]);

      $queteurs = $this->queteurDBService->getQueteurByAnonymizationToken($this->validatedData["anonymization_token"],$ulId, $roleId);

      $this->response->getBody()->write(json_encode($queteurs));

      return $this->response;
    }

    $validations = [
      ClientInputValidatorSpecs::withInteger('pageNumber'         , $this->queryParams, 100 , false    ),
      ClientInputValidatorSpecs::withInteger('rowsPerPage'        , $this->queryParams, 100 , false    ),
      ClientInputValidatorSpecs::withString ("q"                  , $this->queryParams, 100, false    ),
      ClientInputValidatorSpecs::withInteger('redquest_registered', $this->queryParams, 3   , false    ),
      ClientInputValidatorSpecs::withInteger('user_role'          , $this->queryParams, 4   , false    ),
      ClientInputValidatorSpecs::withInteger('searchType'         , $this->queryParams, 5   , false    ),
      ClientInputValidatorSpecs::withInteger('secteur'            , $this->queryParams, 10  , false    ),
      ClientInputValidatorSpecs::withBoolean("active"             , $this->queryParams, false , true ),
      ClientInputValidatorSpecs::withBoolean("rcqUser"            , $this->queryParams, false , false),
      ClientInputValidatorSpecs::withBoolean("rcqUserActif"       , $this->queryParams, false , false),
      ClientInputValidatorSpecs::withBoolean("benevoleOnly"       , $this->queryParams, false , false),
      ClientInputValidatorSpecs::withString ("queteurIds"         , $this->queryParams, 50 , false    ),
      ClientInputValidatorSpecs::withInteger('QRSearchType'       , $this->queryParams, 5   , false    ),
      ClientInputValidatorSpecs::withInteger('admin_ul_id'        , $this->queryParams, 1000, false    )
    ];


    $this->validateSentData($validations);

    if(array_key_exists('admin_ul_id',$this->queryParams) && $roleId == 9)
    {
      $this->validatedData['ul_id'] = $this->validatedData["admin_ul_id"];
    }
    else
    {
      $this->validatedData['ul_id'] = $ulId;
    }
    
    $pageableRequest = new PageableRequestEntity($this->validatedData);
    
    $queteurs = $this->queteurDBService->searchQueteurs($pageableRequest);

    $this->response->getBody()->write(json_encode($queteurs));

    return $this->response;
  }
}
