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

/********************************* Authentication ****************************************/


/**
 * Get username/password from request, trim it.
 * If size is above 10 for username or 20 for the password  ==> authentication error
 * Then get the user object from username and check the passwords
 * if authentication succeed, get the Queteur object
 * Build the JWT Token with id, username, ulId, queteurId, roleId inside it.
 *
 */

$app->post('/authenticate', function($request, $response, $args) use ($app)
{
  try
  {
    $username = trim($request->getParsedBodyParam("username"    ));
    $password = trim($request->getParsedBodyParam("password"    ));
    $token    = trim($request->getParsedBodyParam("token"       ));
    $remoteIP =      $request->getServerParams ()['REMOTE_ADDR'  ];

    //refusing null, empty, big
    if($username        == null || $password         == null || $token         == null || $remoteIP         == null ||
      strlen($username) == 0    || strlen($password) == 0    || strlen($token) == 0    || strlen($remoteIP) == 0    ||
      strlen($username) >  20   || strlen($password) >  20   || strlen($token) >  500  || strlen($remoteIP) >  15     )
    {

      $this->logger->addError("Login attempted with username or password or token or remoteIP exceeding limits", array('remoteIp'=>substr($remoteIP, 0,50)));

      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(['error'=>'username or password error. Code 1']));

      return $response401;
    }

    $this->logger->addInfo("ReCaptcha checking user for login", array('remoteIp'=>$remoteIP, 'username' => $username, 'token' => $token));
    $reCaptchaResponseCode = $this->reCaptcha->verify($token, "rcq/login", $remoteIP, $username);

    if($reCaptchaResponseCode > 0)
    {// error
      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"an error occurred. Code $reCaptchaResponseCode"]));

      return $response401;
    }

    $user           = $this->userDBService->getUserInfoWithNivol($username);

    // $this->logger->addDebug("User Entity for user id='".$user->id."' nivol='".$username."'".print_r($user, true));

    if($user instanceof UserEntity &&
      password_verify($password, $user->password))
    {
      $queteur = $this->queteurDBService    ->getQueteurById    ($user   ->queteur_id);
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

      $this->logger->addError("Authentication failed, wrong password, for user id='".$user->id."' nivol='".$username."'");
      $this->userDBService->registerFailedLogin($user->id);

      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 2.1']));

      return $response401;
    }
    else
    {
      $this->logger->addError("Authentication failed, wrong password, for user nivol='".$username."', response is not a UserEntity : ".print_r($user,true));
      $this->userDBService->registerFailedLogin($user->id);

      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 2.2']));

      return $response401;
    }


  }
  catch(\Exception $e)
  {
    $this->logger->addError("unexpected exception during authentication", array("Exception"=>$e));

    $response401 = $response->withStatus(401);
    $response401->getBody()->write(json_encode(["error"=>'username or password error. Code 3.']));
    return $response401;
  }
});


/**
 * for the user with the specified nivol, update the 'init_passwd_uuid' and 'init_passwd_date' field in DB
 * and send the user an email with a link, to reach the reset password form
 *
 */

$app->post('/sendInit', function ($request, $response, $args) use ($app)
{
  $username = "";
  try
  {
    $username = trim($request->getParsedBodyParam("username"));
    $token    = trim($request->getParsedBodyParam("token"   ));
    $remoteIP =      $request->getServerParams ()['REMOTE_ADDR'];

    //refusing null, empty, big
    if($username        == null ||
      strlen($username) == 0    ||
      strlen($username) >  20    )
    {

      $this->logger->addError("sendInit attempted with username or token exceeding limits", array('remoteIp'=>$remoteIP));

      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode( ["error" => "an error occurred. Code -1"]));

      return $response401;
    }

    $reCaptchaResponseCode = $this->reCaptcha->verify($token, "rcq/sendInit", $remoteIP, $username);

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
    $this->logger->addError("unexpected exception during sendInit", array("username"=>$username, "Exception"=>$e));
    $response->getBody()->write(json_encode(["success"=>false]));
    return $response;
  }

});


/**
 * Get user information from the UUID
 *
 */
$app->get('/getInfoFromUUID', function ($request, $response, $args) use ($app)
{
  try
  {
    $uuid  = trim($request->getParam("uuid"));
    $token = trim($request->getParam("token"));

    $remoteIP =      $request->getServerParams ()['REMOTE_ADDR'];

    $reCaptchaResponseCode = $this->reCaptcha->verify($token, "rcq/getUserInfoWithUUID", $remoteIP, "getInfoFromUUID");

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
        "first_name"    => $queteur->first_name,
        "last_name"     => $queteur->last_name ,
        "email"         => $queteur->email     ,
        "mobile"        => $queteur->mobile    ,
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
    $this->logger->addError("unexpected exception during getInfoFromUUID", array("uuid"=>$uuid, "Exception"=>$e));

    $response->getBody()->write(json_encode(["success"=>false]));
    return $response;
  }
});

/**
 * save new password of user
 */
$app->post('/resetPassword', function ($request, $response, $args) use ($app)
{
  try
  {
    $uuid     = trim($request->getParsedBodyParam("uuid"    ));
    $password = trim($request->getParsedBodyParam("password"));
    $token    = trim($request->getParsedBodyParam("token"));
    $remoteIP =      $request->getServerParams ()['REMOTE_ADDR'];

    $reCaptchaResponseCode = $this->reCaptcha->verify($token, "rcq/resetPassword", $remoteIP, "getInfoFromUUID");

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
    $this->logger->addError("unexpected exception during getInfoFromUUID", array("uuid"=>$uuid, "Exception"=>$e));
    $response->getBody()->write(json_encode(["success"=>false]));
    return $response;
  }
});
