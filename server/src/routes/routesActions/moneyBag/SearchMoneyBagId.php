<?php
namespace RedCrossQuest\routes\routesActions\moneyBag;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\MoneyBagDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;

class SearchMoneyBagId extends Action
{
  /**
   * @var MoneyBagDBService           $moneyBagDBService
   */
  private MoneyBagDBService $moneyBagDBService;


  /**
   * @param LoggerInterface      $logger
   * @param ClientInputValidator $clientInputValidator
   * @param MoneyBagDBService    $moneyBagDBService
   */
  public function __construct(LoggerInterface             $logger,
                              ClientInputValidator        $clientInputValidator,
                              MoneyBagDBService           $moneyBagDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->moneyBagDBService = $moneyBagDBService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("q"   , $this->queryParams, 30 , true),
        ClientInputValidatorSpecs::withString("type", $this->queryParams,  4 , true)
      ]);

    $query         = $this->validatedData["q"   ];
    $type          = $this->validatedData["type"];
    $ulId          = $this->decodedToken->getUlId ();

    $moneyBagIds = $this->moneyBagDBService->searchMoneyBagId($query, $type, $ulId);

    $this->response->getBody()->write(json_encode($moneyBagIds));

    return $this->response;
  }
}
