<?php




namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class GetMoneyBagDetails extends Action
{
  /**
   * @var TroncQueteurDBService          $troncQueteurDBService
   */
  private $troncQueteurDBService;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncQueteurDBService $troncQueteurDBService
   */
  public function __construct(LoggerInterface             $logger,
                              ClientInputValidator        $clientInputValidator,
                              TroncQueteurDBService       $troncQueteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService       = $troncQueteurDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString ('moneyBagId' , $this->getParam('moneyBagId'), 20, true),
        ClientInputValidatorSpecs::withBoolean('coin'       , $this->getParam('coin'      ), true)
      ]);

    $bagId         = $this->validatedData["moneyBagId"];
    $coin          = $this->validatedData["coin"      ];
    $ulId          = $this->decodedToken->getUlId ();

    $this->logger->info("Get moneyBagDetails",["bagId"=>$bagId, "coin"=>$coin]);
    if($coin)
    {
      $bagData = $this->troncQueteurDBService->getCoinsMoneyBagDetails($ulId, $bagId);
    }
    else
    {
      $bagData = $this->troncQueteurDBService->getBillsMoneyBagDetails($ulId, $bagId);
    }

    $this->response->getBody()->write(json_encode($bagData));

    return $this->response;
  }
}
