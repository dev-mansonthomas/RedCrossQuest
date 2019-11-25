<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\Entity\UserEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\ReCaptchaService;


class ResetPassword extends Action
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
   * @var EmailBusinessService
   */
  private $emailBusinessService;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ReCaptchaService $reCaptchaService
   * @param UserDBService $userDBService
   * @param QueteurDBService $queteurDBService
   * @param EmailBusinessService $emailBusinessService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              ReCaptchaService        $reCaptchaService,
                              UserDBService           $userDBService,
                              QueteurDBService        $queteurDBService,
                              EmailBusinessService    $emailBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    
    $this->reCaptchaService       = $reCaptchaService;
    $this->userDBService          = $userDBService;
    $this->queteurDBService       = $queteurDBService;
    $this->emailBusinessService   = $emailBusinessService;
  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("uuid"    , $this->parsedBody["uuid"     ], 36   , true, ClientInputValidator::$UUID_VALIDATION),
        ClientInputValidatorSpecs::withString("password", $this->parsedBody["password" ], 60   , true),
        ClientInputValidatorSpecs::withString("token"   , $this->parsedBody["token"    ], 1500 , true),
      ]);

    $uuid     = $this->validatedData["uuid"];
    $password = $this->validatedData["password"];
    $token    = $this->validatedData["token"];

    Logger::dataForLogging(new LoggingEntity(null, ["uuid"=>$uuid]));

    $reCaptchaResponseCode = $this->reCaptchaService->verify($token, "rcq/resetPassword", "getInfoFromUUID");

    if($reCaptchaResponseCode > 0)
    {// error
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"an error occurred. Code $reCaptchaResponseCode"]));

      return $response401;
    }

    $user          = $this->userDBService->getUserInfoWithUUID($uuid);

    if($user instanceof UserEntity)
    {
      $success = $this->userDBService->resetPassword($uuid, $password);

      if($success)
      {
        $queteur          = $this->queteurDBService->getQueteurById($user->queteur_id);

        $this->emailBusinessService->sendResetPasswordEmailConfirmation($queteur);

        $this->logger->error("sendResetPasswordEmailConfirmation");

        $this->response->getBody()->write(json_encode(["success"=>true, "email" => $queteur->email]));
        return $this->response;
      }
    }

    //the user do not have an account
    $this->response->getBody()->write(json_encode(["success"=>false]));
    return $this->response;

  }
}
