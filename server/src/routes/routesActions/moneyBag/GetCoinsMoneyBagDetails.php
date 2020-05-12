<?php
namespace RedCrossQuest\routes\routesActions\moneyBag;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\MoneyBagDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;

class GetCoinsMoneyBagDetails extends Action
{
  /**
   * @var MoneyBagDBService           $moneyBagDBService
   */
  private $moneyBagDBService;


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
        ClientInputValidatorSpecs::withString ('id' ,  $this->args, 20, true)
      ]);

    $bagId = $this->validatedData["id"];
    $ulId  = $this->decodedToken->getUlId ();

    $this->logger->info("Get Coins moneyBagDetails",["bagId"=>$bagId]);

    $bagData = $this->moneyBagDBService->getCoinsMoneyBagDetails($ulId, $bagId);

    $this->response->getBody()->write(json_encode($bagData));

    return $this->response;
  }
}
