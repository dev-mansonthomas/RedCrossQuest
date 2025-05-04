<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class UpdateQueteur extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private QueteurDBService $queteurDBService;


  /**
   * @var UserDBService $userDBService
   */
  private UserDBService $userDBService;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService $queteurDBService
   * @param UserDBService $userDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService        $queteurDBService,
                              UserDBService           $userDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService     = $queteurDBService;
    $this->userDBService        = $userDBService;

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

    $oldQueteurEntity = $this->queteurDBService->getQueteurById($queteurEntity->id, $roleId == 9? null : $ulId);


    //restore the leading +
    $queteurEntity->mobile = "+".$queteurEntity->mobile;
    $this->queteurDBService->update($queteurEntity, $ulId, $roleId, $userId);
    if($queteurEntity->nivol != $oldQueteurEntity->nivol && array_key_exists('user' , $this->parsedBody) && $this->parsedBody['user']['id']>0)
    {
      $user = $this->userDBService->getUserInfoWithQueteurId($queteurEntity->id, $ulId, $roleId);
      $this->userDBService->updateNivol($user->id, $queteurEntity->nivol, $ulId, $roleId);
    }

    return $this->response;
  }
}
