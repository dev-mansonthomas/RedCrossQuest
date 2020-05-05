<?php




namespace RedCrossQuest\routes\routesActions\unitesLocales;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


class GetUniteLocale extends Action
{


  /**
   * @var UniteLocaleDBService          $uniteLocaleDBService
   */
  private $uniteLocaleDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UniteLocaleDBService $uniteLocaleDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              UniteLocaleDBService          $uniteLocaleDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleDBService         = $uniteLocaleDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId();

    $this->response->getBody()->write(json_encode($this->uniteLocaleDBService         ->getUniteLocaleById   ($ulId)));

    return $this->response;
  }
}
