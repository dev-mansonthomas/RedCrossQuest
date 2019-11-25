<?php




namespace RedCrossQuest\routes\routesActions\troncs;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class ListTroncs extends Action
{
  /**
   * @var TroncDBService          $troncDBService
   */
  private $troncDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncDBService          $troncDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              TroncDBService          $troncDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncDBService = $troncDBService;

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
        ClientInputValidatorSpecs::withBoolean("active", $this->getParam('active'), false     , null),
        ClientInputValidatorSpecs::withInteger("type"  , $this->getParam('type'  ), 5       , false, null),
        ClientInputValidatorSpecs::withInteger("q"     , $this->getParam('q'     ), 1000000 , false, null),
      ]);

    $active = $this->validatedData["active"];
    $type   = $this->validatedData["type"];
    $q      = $this->validatedData["q"];

    $ulId   = $this->decodedToken->getUlId();
    $troncs = $this->troncDBService->getTroncs($q, $ulId, $active, $type);

    $this->response->getBody()->write(json_encode($troncs));

    return $this->response;
  }
}