<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use DI\Annotation\Inject;
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
use RedCrossQuest\Service\ReCaptchaService;


class GetUserInfoFromUUIDAction extends Action
{
  /**
   * @var ReCaptchaService
   */
  private $reCaptchaService;
  /**
   * @var UserDBService
   */
  private $userDBService;
  /**
   * @var QueteurDBService
   */
  private $queteurDBService;
  /**
   * @Inject("settings")
   * @var array settings
   */
  protected $settings;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ReCaptchaService $reCaptchaService
   * @param UserDBService $userDBService
   * @param QueteurDBService $queteurDBService
   *
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              ReCaptchaService        $reCaptchaService,
                              UserDBService           $userDBService,
                              QueteurDBService        $queteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    
    $this->reCaptchaService       = $reCaptchaService;
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
    $token = $this->validatedData["token"];

    Logger::dataForLogging(new LoggingEntity(null, ["uuid"=>$uuid]));

    $appUrl = $this->settings['appSettings']['appUrl'];
    if($appUrl == "http://localhost:3000/")
    {//ReCaptcha fails systematically for localhost ==> disable
      $reCaptchaResponseCode = 0;
    }
    else
    {
      $reCaptchaResponseCode = $this->reCaptchaService->verify($token, "rcq/getUserInfoWithUUID", "getInfoFromUUID");
    }


    if($reCaptchaResponseCode > 0)
    {// error
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"an error occurred. Code $reCaptchaResponseCode"]));

      return $response401;
    }

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
