<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class SearchSimilarQueteurs extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;

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
   * @throws \Exception
   */
  protected function action(): Response
  {
    $ulId     = $this->decodedToken->getUlId();

    $this->validateSentData([
      ClientInputValidatorSpecs::withString("first_name", $this->getParam('first_name'), 100 , false),
      ClientInputValidatorSpecs::withString("last_name" , $this->getParam('last_name' ), 100 , false),
      ClientInputValidatorSpecs::withString("nivol"     , $this->getParam('nivol'     ), 15  , false)
    ]);

    $firstName  = $this->validatedData["first_name"];
    $lastName   = $this->validatedData["last_name"];
    $nivol      = $this->validatedData["nivol"];

    if(empty($firstName) && empty($lastName) && empty($nivol))
    {//if nothing is give, return empty array
      $this->response->getBody()->write(json_encode([]));
      return $this->response;
    }

    $queteurs = $this->queteurDBService->searchSimilarQueteur($ulId, $firstName, $lastName, $nivol);
    $this->response->getBody()->write(json_encode($queteurs));

    return $this->response;
  }
}
