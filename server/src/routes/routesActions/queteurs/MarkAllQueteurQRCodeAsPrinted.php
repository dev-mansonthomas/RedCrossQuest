<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class MarkAllQueteurQRCodeAsPrinted extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private QueteurDBService $queteurDBService;

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
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId     = $this->decodedToken->getUlId();
    $printed = $this->request->getQueryParams()['printed']??null;

    if (!in_array($printed, ['true', 'false'], true))
    {
      $error = ['error' => "Invalid value for 'printed'. Expected 'true' or 'false'."];
      return $this->response->withStatus(400)->withHeader('Content-Type', 'application/json')->write(json_encode($error));
    }
    $printedBoolean = $printed === 'true';
    $this->queteurDBService->markAllAsPrinted($ulId, $printedBoolean);
    return $this->response;
  }
}
