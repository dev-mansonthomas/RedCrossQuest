<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/4017
 * Time: 18:36
 */
require '../../vendor/autoload.php';

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use \RedCrossQuest\Entity\UserEntity;

use RedCrossQuest\Service\ClientInputValidator;

/********************************* Authentication ****************************************/


/**
 * Get username/password from request, trim it.
 * If size is above 10 for username or 20 for the password  ==> authentication error
 * Then get the user object from username and check the passwords
 * if authentication succeed, get the Queteur object
 * Build the JWT Token with id, username, ulId, queteurId, roleId inside it.
 *
 */

$app->post(getPrefix().'/authenticate', function($request, $response, $args) use ($app)
{
  try
  {
    $username = $this->clientInputValidator->validateString("username", $request->getParsedBodyParam("username" ), 20  , true );
    $password = $this->clientInputValidator->validateString("password", $request->getParsedBodyParam("password" ), 60  , true );

    $this->logger->info("Route : authenticate: $username");
    $token    = $this->clientInputValidator->validateString("token"   , $request->getParsedBodyParam("token"    ), 500 , true );
    $this->logger->info("ReCaptcha checking user for login", array('username' => $username, 'token' => $token));

    $reCaptchaResponseCode = $this->reCaptcha->verify($token, "rcq/login", $username);

    if($reCaptchaResponseCode > 0)
    {// error
      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"An error occurred - ReCode $reCaptchaResponseCode"]));

      return $response401;
    }

    //$this->logger->info("getUserInfoWithNivol");
    $user           = $this->userDBService->getUserInfoWithNivol($username);

    // $this->logger->debug("User Entity for user id='".$user->id."' nivol='".$username."'".print_r($user, true));

    if($user instanceof UserEntity &&
      password_verify($password, $user->password))
    {
      //$this->logger->info("getQueteurById");
      $queteur = $this->queteurDBService    ->getQueteurById    ($user   ->queteur_id);
      //$this->logger->info("getUniteLocaleById");
      $ul      = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id     );

      $signer = new Sha256();

      $settings       = $this->get('settings');

      $jwtSecret      = $settings['jwt'        ]['secret'        ];
      $issuer         = $settings['jwt'        ]['issuer'        ];
      $audience       = $settings['jwt'        ]['audience'      ];
      $deploymentType = $settings['appSettings']['deploymentType'];
      $sessionLength  = $settings['appSettings']['sessionLength' ];

      $jwtToken = (new Builder())
        ->setIssuer    ($issuer       ) // Configures the issuer (iss claim)
        ->setAudience  ($audience     ) // Configures the audience (aud claim)
        ->setIssuedAt  (time()        ) // Configures the time that the token was issue (iat claim)
        ->setNotBefore (time()        ) // Configures the time that the token can be used (nbf claim)
        ->setExpiration(time() + $sessionLength * 3600 ) // Configures the expiration time of the token (nbf claim)
        //Business Payload
        ->set          ('username' , $username      )
        ->set          ('id'       , $user->id      )
        ->set          ('ulId'     , $queteur->ul_id)
        ->set          ('ulName'   , $ul->name      )
        ->set          ('ulMode'   , $ul->mode      )
        ->set          ('queteurId', $queteur->id   )
        ->set          ('roleId'   , $user->role    )
        ->set          ('d'        , $deploymentType)

        ->sign         ($signer    , $jwtSecret     ) // Sign the token
        ->getToken     ();                           // Retrieves the generated token (it's an object with __toString() implemented



      $response->getBody()->write(json_encode(["token"=>$jwtToken->__toString()]));

      $this->userDBService->registerSuccessfulLogin($user->id);

      //generate a spotfire token at the same time
      //Token will be retrieved by client on a separate REST Call
      $this->spotfireAccessDBService->grantAccess($user->id , $queteur->ul_id,   $sessionLength);

      return $response;
    }
    else if($user instanceof UserEntity)
    {//we found the user, but password is not good

      $this->logger->error("Authentication failed, wrong password, for user id='".$user->id."' nivol='".$username."'");
      $this->userDBService->registerFailedLogin($user->id);

      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 2.1']));

      return $response401;
    }
    else
    {
      $this->logger->error("Authentication failed, wrong password, for user nivol='".$username."', response is not a UserEntity : ".print_r($user,true));
      $this->userDBService->registerFailedLogin($user->id);

      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 2.2']));

      return $response401;
    }
  }
  catch(\Exception $e)
  {
    $this->logger->error("unexpected exception during authentication", array("Exception"=>$e));

    $response401 = $response->withStatus(401);
    $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 3.', "ex"=>json_encode($e)]));
    return $response401;
  }
});


/**
 * for the user with the specified nivol, update the 'init_passwd_uuid' and 'init_passwd_date' field in DB
 * and send the user an email with a link, to reach the reset password form
 *
 */

$app->post(getPrefix().'/sendInit', function ($request, $response, $args) use ($app)
{
  $username = "";
  try
  {
    $username = $this->clientInputValidator->validateString("username", $request->getParsedBodyParam("username" ), 20  , true);
    $this->logger->info("Route : sendInit: $username");
    

    $token    = $this->clientInputValidator->validateString("token"   , $request->getParsedBodyParam("token"    ), 500 , true);


    $reCaptchaResponseCode = $this->reCaptcha->verify($token, "rcq/sendInit", $username);

    if($reCaptchaResponseCode > 0)
    {// error
      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>'an error occurred. Code $reCaptchaResponseCode']));

      return $response401;
    }

    $uuid          = $this->userDBService->sendInit($username);

    if($uuid != null)
    {
      $queteur = $this->queteurDBService->getQueteurByNivol($username);
      //$this->logger->debug(print_r($queteur,true));
      $this->mailer->sendInitEmail($queteur, $uuid);

      //protect email address
      $email = substr($queteur->email, 0, 1)."...@...".substr($queteur->email,-5, 5);
      $response->getBody()->write(json_encode(["success"=>true,"email"=>$email]));
      return $response;

    }
    else
    {//the user do not have an account
      $response->getBody()->write(json_encode(["success"=>false]));
      return $response;
    }
  }
  catch(\Exception $e)
  {
    $this->logger->error("unexpected exception during sendInit", array("username"=>$username, "Exception"=>$e));
    //TODO remove exception
    $response->getBody()->write(json_encode(["success"=>false, "ex"=>$e]));
    return $response;
  }

});


/**
 * Get user information from the UUID
 * Used in the reinit password process
 *
 */
$app->get(getPrefix().'/getInfoFromUUID', function ($request, $response, $args) use ($app)
{
  $uuid ="";
  try
  {
    $uuid     = $this->clientInputValidator->validateString("uuid"    , $request->getParam("uuid"     ), 36  , true, ClientInputValidator::$UUID_VALIDATION);
    $token    = $this->clientInputValidator->validateString("token"   , $request->getParam("token"    ), 500 , true );

    $appUrl = $this->get('settings')['appSettings']['appUrl'];
    if($appUrl == "http://localhost:3000/")
    {//ReCaptcha fails systematically for localhost ==> disable
      $reCaptchaResponseCode = 0;
    }
    else
    {
      $reCaptchaResponseCode = $this->reCaptcha->verify($token, "rcq/getUserInfoWithUUID", "getInfoFromUUID");
    }


    if($reCaptchaResponseCode > 0)
    {// error
      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"an error occurred. Code $reCaptchaResponseCode"]));

      return $response401;
    }

    if (strlen($uuid) != 36)
    {
      $response->getBody()->write(json_encode(["success"=>false]));
      return $response;
    }

    $user = $this->userDBService->getUserInfoWithUUID($uuid);
    if ($uuid != null)
    {
      $queteur = $this->queteurDBService->getQueteurById($user->queteur_id);

      $response->getBody()->write(json_encode([
        "success"       => true,
        "nivol"         => $queteur->nivol ]));

      return $response;

    }
    else
    {//the user do not have an account
      $response->getBody()->write(json_encode(["success"=>false]));
      return $response;
    }
  }
  catch(\Exception $e)
  {
    $this->logger->error("unexpected exception during getInfoFromUUID", array("uuid"=>$uuid, "Exception"=>$e));

    $response->getBody()->write(json_encode(["success"=>false]));
    return $response;
  }
});

/**
 * save new password of user
 */
$app->post(getPrefix().'/resetPassword', function ($request, $response, $args) use ($app)
{
  try
  {
    $uuid     = $this->clientInputValidator->validateString("uuid"    , $request->getParsedBodyParam("uuid"     ), 36  , true, ClientInputValidator::$UUID_VALIDATION);
    $password = $this->clientInputValidator->validateString("password", $request->getParsedBodyParam("password" ), 60  , true );
    $token    = $this->clientInputValidator->validateString("token"   , $request->getParsedBodyParam("token"    ), 500 , true );

    $reCaptchaResponseCode = $this->reCaptcha->verify($token, "rcq/resetPassword", "getInfoFromUUID");

    if($reCaptchaResponseCode > 0)
    {// error
      $response401 = $response->withStatus(401);
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

        $this->mailer->sendResetPasswordEmailConfirmation($queteur);

        $response->getBody()->write(json_encode(["success"=>true, "email" => $queteur->email]));
        return $response;
      }
    }

    //the user do not have an account
    $response->getBody()->write(json_encode(["success"=>false]));
    return $response;

  }
  catch(\Exception $e)
  {
    $this->logger->error("unexpected exception during resetPassword", array("uuid"=>$uuid, "Exception"=>$e));
    $response->getBody()->write(json_encode(["success"=>false]));
    return $response;
  }
});
