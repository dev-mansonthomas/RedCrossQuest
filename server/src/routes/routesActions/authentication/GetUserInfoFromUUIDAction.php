<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class GetUserInfoFromUUIDAction extends Action
{
  /**
   * @var UserDBService
   */
  private UserDBService $userDBService;
  /**
   * @var QueteurDBService
   */
  private QueteurDBService $queteurDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UserDBService $userDBService
   * @param QueteurDBService $queteurDBService
   *
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              UserDBService           $userDBService,
                              QueteurDBService        $queteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);

    $this->userDBService          = $userDBService;
    $this->queteurDBService       = $queteurDBService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {

    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("uuid"    , $this->queryParams, 36   , true, ClientInputValidator::$UUID_VALIDATION),
        ClientInputValidatorSpecs::withString("token"   , $this->queryParams, 1500 , true),
      ]);

    $uuid  = $this->validatedData["uuid"];

    Logger::dataForLogging(new LoggingEntity(null, ["uuid"=>$uuid]));

    //TODO re-enable a captcha check here (ReCaptcha v2 or equivalent) before hitting the DB.

    if (strlen($uuid) != 36)
    {
      $this->response->getBody()->write(json_encode(new GetUserInfoFromUUIDResponse(false)));
      return $this->response;
    }

    $user = $this->userDBService->getUserInfoWithUUID($uuid);
    if ($user != null)
    {
      $queteur = $this->queteurDBService->getQueteurById($user->queteur_id);
      $this->response->getBody()->write(json_encode(new GetUserInfoFromUUIDResponse(true, $queteur->nivol)));
      return $this->response;

    }
    else
    {//the user do not have an account
      $this->logger->info("No account found with UUID '".$uuid."'");
      $this->response->getBody()->write(json_encode(new GetUserInfoFromUUIDResponse(false)));
      return $this->response;
    }

  }
}
