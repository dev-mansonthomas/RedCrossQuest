<?php




namespace RedCrossQuest\routes\routesActions\users;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\UserEntity;
use RedCrossQuest\Exception\UserAlreadyExistsException;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class CreateUser extends Action
{
  /**
   * @var UserDBService  $userDBService
   */
  private $userDBService;

  /**
   * @var QueteurDBService $queteurDBService
   */
  private $queteurDBService;

  /**
   * @var EmailBusinessService          $emailBusinessService
   */
  private $emailBusinessService;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UserDBService $userDBService
   * @param QueteurDBService $queteurDBService
   * @param EmailBusinessService $emailBusinessService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              UserDBService                 $userDBService,
                              QueteurDBService              $queteurDBService,
                              EmailBusinessService          $emailBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);

    $this->userDBService        = $userDBService;
    $this->queteurDBService     = $queteurDBService;
    $this->emailBusinessService = $emailBusinessService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $userEntity     = new UserEntity($this->parsedBody, $this->logger);
    $ulId           = $this->decodedToken->getUlId  ();
    $roleId         = $this->decodedToken->getRoleId();
    $queteur        = $this->queteurDBService->getQueteurById($userEntity->queteur_id, $roleId ==9 ? null: $ulId);

    //check NIVOL has not been changed
    if($userEntity->nivol != $queteur->nivol)
    {
      throw new Exception("UserEntity NIVOL (from web form) & Queteur NIVOL (from DB) do not match ('".$userEntity->nivol."'!='".$queteur->nivol."')");
    }

    if($queteur->ul_id != $ulId )
    {
      if($roleId != 9)
      {
        throw new Exception("current user is trying to create an RCQ user for another UL and is not super Admin");
      }
      else
      {
        $this->logger->info("SuperAdmin is creating an user for another UL ".$queteur->ul_id." NIVOL:'".$userEntity->nivol."' - QueteurId:'".$userEntity->queteur_id."'");
      }
    }

    try
    {
      $this->userDBService->insert($userEntity->nivol, $userEntity->queteur_id);
    }
    catch(UserAlreadyExistsException $exception)
    {
      $this->logger->error($exception->getMessage(), [$exception->users, $exception]);
      $this->response->getBody()->write(json_encode(["error" =>$exception->getMessage()]));
      return $this->response;
    }

    $user = $this->userDBService->getUserInfoWithQueteurId($userEntity->queteur_id, $ulId, $roleId);
    $uuid = $this->userDBService->sendInit                ($userEntity->nivol, true);

    $this->emailBusinessService->sendInitEmail($queteur, $uuid, true);

    $this->response->getBody()->write(json_encode($user));

    return $this->response;
  }
}
