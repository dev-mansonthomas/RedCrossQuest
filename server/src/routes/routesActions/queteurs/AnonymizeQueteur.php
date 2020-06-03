<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class AnonymizeQueteur extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;

  /**
   * @var UserDBService           $userDBService
   */
  private $userDBService;

  /**
   * @var EmailBusinessService    $emailBusinessService
   */
  private $emailBusinessService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService $queteurDBService
   * @param UserDBService $userDBService
   * @param EmailBusinessService $emailBusinessService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService        $queteurDBService,
                              UserDBService           $userDBService,
                              EmailBusinessService    $emailBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService     = $queteurDBService;
    $this->userDBService        = $userDBService;
    $this->emailBusinessService = $emailBusinessService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId  ();
    $roleId = $this->decodedToken->getRoleId();
    $userId = $this->decodedToken->getUid   ();

    $queteurEntity = new QueteurEntity($this->parsedBody, $this->logger);

    $queteurOriginalData   = $this->queteurDBService->getQueteurById($queteurEntity->id);

    try
    {
      $this->queteurDBService->transactionStart();
      $token                 = $this->queteurDBService->anonymize($queteurOriginalData->id, $ulId, $roleId, $userId);
      $userAnonymised        = $this->userDBService   ->anonymise($queteurOriginalData->id, $ulId, $roleId);
      $this->queteurDBService->transactionCommit();
    }
    catch(Exception $e)
    {
      $this->queteurDBService->transactionRollback();
      throw $e;
    }

    if(isset($queteurOriginalData->email) && strlen($queteurOriginalData->email)>=5)
      $this->emailBusinessService->sendAnonymizationEmail($queteurOriginalData, $token, $userAnonymised);

    $queteurAnonymizedData = $this->queteurDBService->getQueteurById($queteurEntity->id);

    $this->response->getBody()->write(json_encode($queteurAnonymizedData));

    return $this->response;
  }
}
