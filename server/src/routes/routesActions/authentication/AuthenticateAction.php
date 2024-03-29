<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use Exception;
use Lcobucci\JWT\Configuration;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\SpotfireAccessDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\Entity\UserEntity;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\ReCaptchaService;


class AuthenticateAction extends AuthenticateAbstractAction
{

  /**
   * @var ReCaptchaService
   */
  private ReCaptchaService $reCaptchaService;
  /**
   * @var UserDBService
   */
  private UserDBService $userDBService;
  /**
   * @var QueteurDBService
   */
  private QueteurDBService $queteurDBService;
  /**
   * @var UniteLocaleDBService
   */
  private UniteLocaleDBService $uniteLocaleDBService;
  /**
   * @var SpotfireAccessDBService
   */
  private SpotfireAccessDBService $spotfireAccessDBService;


  /**
   * @param LoggerInterface         $logger
   * @param ClientInputValidator    $clientInputValidator
   * @param Configuration           $JWTConfiguration
   * @param ReCaptchaService        $reCaptchaService
   * @param UserDBService           $userDBService
   * @param QueteurDBService        $queteurDBService
   * @param UniteLocaleDBService    $uniteLocaleDBService
   * @param SpotfireAccessDBService $spotfireAccessDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              Configuration           $JWTConfiguration,
                              ReCaptchaService        $reCaptchaService,
                              UserDBService           $userDBService,
                              QueteurDBService        $queteurDBService,
                              UniteLocaleDBService    $uniteLocaleDBService,
                              SpotfireAccessDBService $spotfireAccessDBService)
  {
    parent::__construct($logger, $clientInputValidator, $JWTConfiguration);
    
    $this->reCaptchaService       = $reCaptchaService;
    $this->userDBService          = $userDBService;
    $this->queteurDBService       = $queteurDBService;
    $this->uniteLocaleDBService   = $uniteLocaleDBService;
    $this->spotfireAccessDBService= $spotfireAccessDBService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("username", $this->parsedBody, 20   , true),
        ClientInputValidatorSpecs::withString("password", $this->parsedBody, 60   , true),
        ClientInputValidatorSpecs::withString("token"   , $this->parsedBody, 1500 , true),
    ]);

    $username = $this->validatedData["username"];
    $password = $this->validatedData["password"];
    $token    = $this->validatedData["token"   ];

    Logger::dataForLogging(new LoggingEntity(null,  ["username"=>$username]));

    $this->logger->debug("ReCaptcha checking user for login", array('username' => $username, 'token' => $token));

    $reCaptchaResponseCode = $this->reCaptchaService->verify($token, "rcq/login", $username, $this->parsedBody);

    if($reCaptchaResponseCode > 0)
    {// error

      $this->logger->error("authenticate: ReCaptcha error ",
        array('username' => $username, 'token' => $token, 'ReCode'=>$reCaptchaResponseCode));

      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"An error occurred - ReCode $reCaptchaResponseCode"]));

      return $response401;
    }

    $user           = $this->userDBService->getUserInfoWithNivol($username);

    if($user instanceof UserEntity &&
      password_verify($password, $user->password))
    {
      $queteur = $this->queteurDBService    ->getQueteurById    ($user   ->queteur_id);
      $ul      = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id     );
      $jwtToken= $this->getToken($queteur, $ul, $user);
      
      $this->response->getBody()->write(json_encode( new AuthenticationResponse($jwtToken->toString())));

      $this->userDBService->registerSuccessfulLogin($user->id);

      //generate a spotfire token at the same time
      //Token will be retrieved by client on a separate REST Call
      $this->spotfireAccessDBService->grantAccess($user->id , $queteur->ul_id,   $this->settings['appSettings']['sessionLength' ]);

      return $this->response;
    }
    else if($user instanceof UserEntity)
    {//we found the user, but password is not good

      $this->logger->warning("Authentication failed, wrong password", array("user_id"=> $user->id, "nivol" =>$username));
      $this->userDBService->registerFailedLogin($user->id);

      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 2.1']));

      return $response401;
    }
    else
    {
      $this->logger->warning("Authentication failed, response is null, user not found ", array("username" => $username, "user" => $user));
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 2.1']));

      return $response401;
    }
  }
}
