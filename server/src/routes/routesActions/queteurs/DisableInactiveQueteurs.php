<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class DisableInactiveQueteurs extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;

  /**
   * @param LoggerInterface      $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService     $queteurDBService
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
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId  = $this->decodedToken->getUlId();

    $this->validateSentData([
      ClientInputValidatorSpecs::withInteger("yearsOfInactivity", $this->queryParams, 10 , false, 1)
    ]);

    $count = $this->queteurDBService->disableInactiveQueteurs($this->validatedData["yearsOfInactivity"], $ulId);

    $this->response->getBody()->write(json_encode(new CountInactiveQueteursResponse($count)));

    return $this->response;
  }
}
