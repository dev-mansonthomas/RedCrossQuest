<?php
namespace RedCrossQuest\routes\routesActions\moneyBag;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;

class GetBillsMoneyBagDetails extends Action
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
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString ('id' , $this->args['id'], 20, true)
      ]);

    $bagId         = $this->validatedData["id"];
    $ulId          = $this->decodedToken->getUlId ();

    $this->logger->info("Get bills moneyBagDetails",["bagId"=>$bagId]);

    $bagData = $this->troncQueteurDBService->getBillsMoneyBagDetails($ulId, $bagId);

    $this->response->getBody()->write(json_encode($bagData));

    return $this->response;
  }
}
