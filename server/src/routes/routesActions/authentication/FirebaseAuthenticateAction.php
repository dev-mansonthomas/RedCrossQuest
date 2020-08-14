<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use Exception;
use Firebase\Auth\Token\Exception\InvalidToken;
use Kreait\Firebase;
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
use RedCrossQuest\Service\SecretManagerService;


class FirebaseAuthenticateAction extends AuthenticateAbstractAction
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
   * @var UniteLocaleDBService
   */
  private $uniteLocaleDBService;

  /**
   * @var SpotfireAccessDBService
   */
  private $spotfireAccessDBService;
  
  /**
   * @var Firebase\Auth  $firebaseAuth
   */
  private $firebaseAuth;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param SecretManagerService $secretManagerService,
   * @param ReCaptchaService $reCaptchaService
   * @param UserDBService $userDBService
   * @param QueteurDBService $queteurDBService
   * @param UniteLocaleDBService $uniteLocaleDBService
   * @param SpotfireAccessDBService $spotfireAccessDBService
   * @param Firebase\Auth $firebaseAuth
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              SecretManagerService    $secretManagerService,
                              ReCaptchaService        $reCaptchaService,
                              UserDBService           $userDBService,
                              QueteurDBService        $queteurDBService,
                              UniteLocaleDBService    $uniteLocaleDBService,
                              SpotfireAccessDBService $spotfireAccessDBService,
                              Firebase\Auth           $firebaseAuth)
  {
    parent::__construct($logger, $clientInputValidator, $secretManagerService);
    
    $this->reCaptchaService       = $reCaptchaService;
    $this->userDBService          = $userDBService;
    $this->queteurDBService       = $queteurDBService;
    $this->uniteLocaleDBService   = $uniteLocaleDBService;
    $this->spotfireAccessDBService= $spotfireAccessDBService;
    $this->firebaseAuth           = $firebaseAuth;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    //email max size : https://www.rfc-editor.org/errata_search.php?eid=1690
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("email"   , $this->parsedBody, 255  , true),
        ClientInputValidatorSpecs::withString("token"   , $this->parsedBody, 1500 , true),
      ]);

    $email    = $this->validatedData["email"];
    $token    = $this->validatedData["token"];

    //TODO enable recaptcha
    /*
      $this->get(LoggerInterface::class)->debug("ReCaptcha checking user for login", array('username' => $username, 'token' => $token));
      $reCaptchaResponseCode = $this->get(ReCaptchaService::class)->verify($token, "rcq/login", $username);
      if($reCaptchaResponseCode > 0)
      {// error

        $this->get(LoggerInterface::class)->error("authenticate: ReCaptcha error ", array('username' => $username, 'token' => $token, 'ReCode'=>$reCaptchaResponseCode));

        $response401 = $response->withStatus(401);
        $response401->getBody()->write(json_encode(["error" =>"An error occurred - ReCode $reCaptchaResponseCode"]));

        return $response401;
      }
    */

    Logger::dataForLogging(new LoggingEntity(null,  ["email"=>$email]));

    try
    {
      $verifiedIdToken = $this->firebaseAuth->verifyIdToken($token);
    }
    catch (InvalidToken $e)
    {
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"Authentication error"]));

      $this->logger->error("Firebase authentication error", array('email' => $email, 'token' => $token, Logger::$EXCEPTION => $e));

      return $response401;
    }
    $uid    = $verifiedIdToken->getClaim('sub');
    $email  = $verifiedIdToken->getClaim('email');

    //$firebaseUser = $this->firebase->getAuth()->getUser($uid);
    $this->logger->debug("Firebase JWT checked successfully", array('uid' => $uid, 'verifiedIdToken'=>$verifiedIdToken));

    $user           = $this->userDBService->getUserInfoWithEmail($email);

    if($user instanceof UserEntity)
    {
      $queteur  = $this->queteurDBService->getQueteurById($user->queteur_id);
      $ul       = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id);

      $jwtToken = $this->getToken($queteur, $ul, $user);

      $this->response->getBody()->write(json_encode( new AuthenticationResponse($jwtToken->__toString())));
      $this->userDBService->registerSuccessfulLogin($user->id);

      //generate a spotfire token at the same time
      //Token will be retrieved by client on a separate REST Call
      $this->spotfireAccessDBService->grantAccess($user->id, $queteur->ul_id, $this->settings['appSettings']['sessionLength']);

      return $this->response;
    }
    else
    {
      $this->logger->error("Authentication failed, user is not registered in RCQ or not active", array("user_id"=> $user->id, "email" =>$email));
      $this->userDBService->registerFailedLogin($user->id);

      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 2.1']));

      return $response401;
    }
  }
}
