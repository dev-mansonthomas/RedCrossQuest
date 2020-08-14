<?php




namespace RedCrossQuest\routes\routesActions\users;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\UserEntity;
use RedCrossQuest\Exception\UserAlreadyExistsException;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\Logger;


class UpdateUser extends Action
{
  /**
   * @var UserDBService  $userDBService
   */
  private $userDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UserDBService $userDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              UserDBService                 $userDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->userDBService = $userDBService;

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

    $this->logger->info("Updating user activeAndRole", array("decodedToken"=>$this->decodedToken, "updatedUser" => $userEntity));

    if($userEntity->role > $this->decodedToken->getRoleId() || $userEntity->role <= 0)
    {
      $this->logger->info("Connected user is trying to grand higher privilege than his to someone else", array("decodedToken"=>$this->decodedToken, "updatedUser" => $userEntity));
      throw new Exception("PHP Fatal error:  Uncaught exception 'PDOException' with message 'SQLSTATE[42601]: Syntax error: 7 ERROR:  syntax error near ) at line 14 at /application/src/DB/DBService/UserManagementDBService.php:67");
    }

    try
    {
      $numberOfUpdatedRows = $this->userDBService->updateActiveAndRole($userEntity, $ulId, $roleId);
    }
    catch(UserAlreadyExistsException $exception)
    {
      $this->logger->error($exception->getMessage(), ["users"=>$exception->users, Logger::$EXCEPTION=>$exception]);
      $this->response->getBody()->write(json_encode(["error" =>$exception->getMessage()]));
      return $this->response;
    }

    if($numberOfUpdatedRows == 0)
    {
      $this->logger->info("Updating user activeAndRole FAILED, no row updated", array("decodedToken"=>$this->decodedToken, "updatedUser" => $userEntity));
      $this->response->getBody()->write(json_encode($userEntity));//return the original objects
      return $this->response;
    }

    $userEntity = $this->userDBService->getUserInfoWithUserId($userEntity->id, $ulId, $roleId);
    $this->response->getBody()->write(json_encode($userEntity));

    return $this->response;
  }
}
