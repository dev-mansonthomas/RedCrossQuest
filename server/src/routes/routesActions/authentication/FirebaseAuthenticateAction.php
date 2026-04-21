<?php


namespace RedCrossQuest\routes\routesActions\authentication;


use Exception;
use Kreait\Firebase;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Lcobucci\JWT\Configuration;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\SpotfireAccessDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class FirebaseAuthenticateAction extends AuthenticateAbstractAction
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
   * @var UniteLocaleDBService
   */
  private UniteLocaleDBService $uniteLocaleDBService;

  /**
   * @var SpotfireAccessDBService
   */
  private SpotfireAccessDBService $spotfireAccessDBService;
  
  /**
   * @var Firebase\Auth  $firebaseAuth
   */
  private Firebase\Auth $firebaseAuth;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param Configuration $JWTConfiguration
   * @param UserDBService $userDBService
   * @param QueteurDBService $queteurDBService
   * @param UniteLocaleDBService $uniteLocaleDBService
   * @param SpotfireAccessDBService $spotfireAccessDBService
   * @param Firebase\Auth $firebaseAuth
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              Configuration           $JWTConfiguration,
                              UserDBService           $userDBService,
                              QueteurDBService        $queteurDBService,
                              UniteLocaleDBService    $uniteLocaleDBService,
                              SpotfireAccessDBService $spotfireAccessDBService,
                              Firebase\Auth           $firebaseAuth)  //TODO update deprecated code
  {
    parent::__construct($logger, $clientInputValidator, $JWTConfiguration);

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
    catch (FailedToVerifyToken $e)
    {
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"Authentication error"]));

      $this->logger->error("Firebase authentication error", array('email' => $email, 'token' => $token, Logger::$EXCEPTION => $e));

      return $response401;
    }
    $claims = $verifiedIdToken->claims();
    $uid    = $claims->get('sub');
    $email  = $claims->get('email');

    $this->logger->debug("Firebase JWT checked successfully", array('uid' => $uid, 'verifiedIdToken'=>$verifiedIdToken));

    $user     = $this->userDBService->getUserInfoWithEmail($email);
    $queteur  = $this->queteurDBService->getQueteurById($user->queteur_id);
    $ul       = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id);

    $jwtToken = $this->getToken($queteur, $ul, $user);

    $this->response->getBody()->write(json_encode( new AuthenticationResponse($jwtToken->toString())));
    $this->userDBService->registerSuccessfulLogin($user->id);

    //generate a spotfire token at the same time
    //Token will be retrieved by client on a separate REST Call
    $this->spotfireAccessDBService->grantAccess($user->id, $queteur->ul_id, $this->settings['appSettings']['sessionLength']);

    return $this->response;
  }
}
