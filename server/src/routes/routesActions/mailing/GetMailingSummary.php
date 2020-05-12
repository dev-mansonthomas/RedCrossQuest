<?php




namespace RedCrossQuest\routes\routesActions\mailing;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\MailingDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class GetMailingSummary extends Action
{
  /**
   * @var MailingDBService               $mailingDBService
   */
  private $mailingDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param MailingDBService $mailingDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              MailingDBService              $mailingDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->mailingDBService = $mailingDBService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId     = $this->decodedToken->getUlId();
    
    $mailingSummary = $this->mailingDBService->getMailingSummary($ulId);
    $this->response->getBody()->write(json_encode($mailingSummary));

    return $this->response;
  }
}
