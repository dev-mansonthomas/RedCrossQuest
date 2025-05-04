<?php




namespace RedCrossQuest\routes\routesActions\settings;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class UpdateULSettings extends Action
{

  /**
   * @var UniteLocaleDBService          $uniteLocaleDBService
   */
  private UniteLocaleDBService $uniteLocaleDBService;



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
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId();
    $userId = $this->decodedToken->getUid   ();

    $ulEntity = new UniteLocaleEntity($this->parsedBody, $this->logger);
    $this->uniteLocaleDBService->updateUL($ulEntity, $ulId, $userId);

    return $this->response;
  }
}
