<?php




namespace RedCrossQuest\routes\routesActions\ulRegistration;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\ReCaptchaService;


class RegisterNewUL extends Action
{

  /**
   * @var UniteLocaleDBService          $uniteLocaleDBService
   */
  private $uniteLocaleDBService;

  /**
   * @var ReCaptchaService              $reCaptchaService
   */
  private $reCaptchaService;

  /**
   * @var UserDBService        $userDBService
   */
  private $userDBService;

  /**
   * @var EmailBusinessService          $emailBusinessService
   */
  private $emailBusinessService;





  /**
   * @param LoggerInterface      $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ReCaptchaService     $reCaptchaService
   * @param UserDBService        $userDBService
   * @param EmailBusinessService          $emailBusinessService
   * @param UniteLocaleDBService $uniteLocaleDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              ReCaptchaService              $reCaptchaService,
                              UserDBService                 $userDBService,
                              UniteLocaleDBService          $uniteLocaleDBService,
                              EmailBusinessService          $emailBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleDBService = $uniteLocaleDBService;
    $this->reCaptchaService     = $reCaptchaService;
    $this->userDBService        = $userDBService;
    $this->emailBusinessService = $emailBusinessService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("token"   , $this->parsedBody, 1500 , true),
      ]);

    $token    = $this->validatedData["token"   ];

    $reCaptchaResponseCode = $this->reCaptchaService->verify($token, "rcq/registerNewUL", "RegisterNewUL");

    if($reCaptchaResponseCode > 0)
    {// error
      $this->logger->error("registerNewUL: ReCaptcha error ", array('token' => $token, 'ReCode'=>$reCaptchaResponseCode));

      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"An error occurred - ReCode $reCaptchaResponseCode"]));

      return $response401;
    }

    $ulEntity = new UniteLocaleEntity($this->parsedBody, $this->logger);

    try
    {
      $this->userDBService->checkExistingUserWithNivol($ulEntity->admin_nivol);
    }
    catch(Exception $e)
    {
      $this->logger->error("RegisterUL fails because Admin Nivol is already an active user",["ulRegistration"=>$ulEntity, "exception"=>json_encode($e)]);
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"Le nivol entré pour l'administrateur a déjà un compte actif dans RedCrossQuest"]));
      return $response401;
    }

    $registrationId = $this->uniteLocaleDBService->registerNewUL($ulEntity);

    $this->emailBusinessService->newULRegistrationEmail($ulEntity);
    
    $this->response->getBody()->write(json_encode(["registrationId"=>$registrationId]));

    return $this->response;
  }
}
