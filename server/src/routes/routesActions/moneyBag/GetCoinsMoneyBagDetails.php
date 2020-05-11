<?php
namespace RedCrossQuest\routes\routesActions\moneyBag;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;

class GetCoinsMoneyBagDetails extends Action
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
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString ('id' ,  $this->args['id'], 20, true)
      ]);

    $bagId = $this->validatedData["id"];
    $ulId  = $this->decodedToken->getUlId ();

    $this->logger->info("Get Coins moneyBagDetails",["bagId"=>$bagId]);

    $bagData = $this->troncQueteurDBService->getCoinsMoneyBagDetails($ulId, $bagId);

    $this->response->getBody()->write(json_encode($bagData));

    return $this->response;
  }
}
