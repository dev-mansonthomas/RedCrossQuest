<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\ReCaptchaService;


class SendPasswordInitializationMailAction extends Action
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
   * @param EmailBusinessService    $emailBusinessService

   *
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
   *
   */
  protected function action(): Response
  {
    $parsedBody = $this->request->getParsedBody();

    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("username", $parsedBody["username" ], 20   , true),
        ClientInputValidatorSpecs::withString("token"   , $parsedBody["token"    ], 1500 , true),
      ]);

    $username = $this->validatedData["username"];
    $token    = $this->validatedData["token"];

    Logger::dataForLogging(new LoggingEntity(null , ["username"=>$username]));

    $reCaptchaResponseCode = $this->reCaptchaService->verify($token, "rcq/sendInit", $username);

    if($reCaptchaResponseCode > 0)
    {// error

      $this->logger->error("sendInit: ReCaptcha error ", array('username' => $username, 'token' => $token, 'ReCode'=>$reCaptchaResponseCode));

      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>'an error occurred. Code $reCaptchaResponseCode']));

      return $response401;
    }

    $uuid          = $this->userDBService->sendInit($username);

    if($uuid != null)
    {
      $queteur = $this->queteurDBService->getQueteurByNivol($username);
      $this->emailBusinessService->sendInitEmail($queteur, $uuid);
      $this->logger->error("sendInit: mail with uuid sent ", array('username' => $username, 'uuid'=>$uuid));

      //protect email address
      $email = substr($queteur->email, 0, 2)."...@...".substr($queteur->email,-6, 6);
      $this->response->getBody()->write(json_encode(new SendPasswordInitializationMailResponse(true, $email)));
      return $this->response;

    }
    else
    {//the user do not have an account
      $this->logger->error("sendInit: user do not have an account ", array('username' => $username));
      $this->response->getBody()->write(json_encode(new SendPasswordInitializationMailResponse(true)));
      return $this->response;
    }
  }
}
