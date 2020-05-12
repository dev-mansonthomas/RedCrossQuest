<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class UpdateQueteur extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService          $queteurDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService        $queteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService     = $queteurDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId  ();
    $roleId = $this->decodedToken->getRoleId();

    $queteurEntity = new QueteurEntity($this->parsedBody, $this->logger);

    //restore the leading +
    $queteurEntity->mobile = "+".$queteurEntity->mobile;
    $this->queteurDBService->update($queteurEntity, $ulId, $roleId);

    return $this->response;
  }
}
