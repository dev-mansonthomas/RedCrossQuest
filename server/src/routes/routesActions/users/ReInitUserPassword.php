<?php




namespace RedCrossQuest\routes\routesActions\users;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\Entity\UserEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


class ReInitUserPassword extends Action
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
   * @throws \Exception
   */
  protected function action(): Response
  {
    $userEntity     = new UserEntity($this->parsedBody, $this->logger);
    $ulId           = $this->decodedToken->getUlId  ();
    $roleId         = $this->decodedToken->getRoleId();

    $this->logger->info("Updating user activeAndRole", array("decodedToken"=>$this->decodedToken, "updatedUser" => $userEntity));

    $queteur          = $this->queteurDBService->getQueteurById($userEntity->queteur_id, $roleId ==9 ? null: $ulId);
    $uuid             = $this->userDBService   ->sendInit      ($userEntity->nivol     );
    $this->emailBusinessService->sendInitEmail($queteur, $uuid);

    $userEntity = $this->userDBService->getUserInfoWithUserId($userEntity->id, $ulId, $roleId);
    $this->response->getBody()->write(json_encode($userEntity));

    return $this->response;
  }
}
