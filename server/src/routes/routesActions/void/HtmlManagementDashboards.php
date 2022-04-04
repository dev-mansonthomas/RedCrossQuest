<?php

namespace RedCrossQuest\routes\routesActions\void;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;

class HtmlManagementDashboards extends Action
{

  /**
   * @param LoggerInterface       $logger
   * @param ClientInputValidator  $clientInputValidator
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator
  )
  {
    parent::__construct($logger, $clientInputValidator);
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->logger->debug("/rest/html/management/dashboards called");
    $this->response->getBody()->write('');
    return $this->response;
  }
}
