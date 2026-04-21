<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use Exception;
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


class SendPasswordInitializationMailAction extends Action
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
   * @var EmailBusinessService
   */
  private EmailBusinessService $emailBusinessService;
  
  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UserDBService $userDBService
   * @param QueteurDBService $queteurDBService
   * @param EmailBusinessService    $emailBusinessService

   *
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              UserDBService           $userDBService,
                              QueteurDBService        $queteurDBService,
                              EmailBusinessService    $emailBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);

    $this->userDBService          = $userDBService;
    $this->queteurDBService       = $queteurDBService;
    $this->emailBusinessService   = $emailBusinessService;
  }

  /**
   * @return Response
   * @throws Exception
   *
   */
  protected function action(): Response
  {
    $parsedBody = $this->request->getParsedBody();

    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("username", $parsedBody, 20   , true),
        ClientInputValidatorSpecs::withString("token"   , $parsedBody, 1500 , true),
      ]);

    $username = $this->validatedData["username"];

    Logger::dataForLogging(new LoggingEntity(null , ["username"=>$username]));

    //TODO re-enable a captcha check here (ReCaptcha v2 or equivalent) before hitting the DB.

    try
    {
      $uuid          = $this->userDBService->sendInit($username);
    }
    catch(Exception $e)
    {
      $this->logger->warning("sendInit : Error while setting UUID for user with specified username",
        array(
          "passedLogin"=>$username,
          Logger::$EXCEPTION=>$e));
      //No rethrow, response from server must be identical whether a user exist or not.
    }


    if(isset($uuid))
    {
      $queteur = $this->queteurDBService->getQueteurByNivol($username);
      $this->emailBusinessService->sendInitEmail($queteur, $uuid);
      $this->logger->debug("sendInit: mail with uuid sent ", array('username' => $username, 'uuid'=>$uuid));

    }
    else
    {//the user do not have an account
      $this->logger->info("sendInit: user do not have an account ", array('username' => $username));
      //Send identical response
    }
    $this->response->getBody()->write(json_encode(new SendPasswordInitializationMailResponse(true)));
    return $this->response;
  }
}
