<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/4017
 * Time: 18:36
 */

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

use \RedCrossQuest\DBService\UserDBService;
use \RedCrossQuest\DBService\QueteurDBService;
use \RedCrossQuest\DBService\UniteLocaleDBService;

use \RedCrossQuest\Entity\UserEntity;

include_once("../../src/DBService/UserDBService.php");
include_once("../../src/DBService/QueteurDBService.php");


/********************************* Authentication ****************************************/


/**
 * Get username/password from request, trim it.
 * If size is above 10 for username or 20 for the password  ==> authentication error
 * Then get the user object from username and check the passwords
 * if authentication succeed, get the Queteur object
 * Build the JWT Token with id, username, ulId, queteurId, roleId inside it.
 *
 */

$app->post('/authenticate', function ($request, $response, $args) use ($app)
{
  try
  {
    $username = trim($request->getParsedBodyParam("username"));
    $password = trim($request->getParsedBodyParam("password"));

    //refusing null, empty, big
    if($username == null || $password == null ||
      strlen($username) == 0 || strlen($password) == 0 ||
      strlen($username) > 10 || strlen($password) > 20)
    {
      $response401 = $response->withStatus(401);
      $response401->getBody()->write(json_encode("{error:'username or password error. Code 1'}"));

      return $response401;
    }


    $this->logger->addInfo("attempting to login with username='$username' and password size='".strlen($password)."'");

    $userDBService        = new UserDBService       ($this->db, $this->logger);
    $queteurDBService     = new QueteurDBService    ($this->db, $this->logger);
    $uniteLocaleDBService = new UniteLocaleDBService($this->db, $this->logger);


    $user = $userDBService->getUserInfoWithNivol($username);

    $this->logger->addError("User Entity for user id='".$user->id."' nivol='".$username."'".print_r($user, true));

    if($user instanceof UserEntity &&
      password_verify($password, $user->password))
    {
      $queteur = $queteurDBService    ->getQueteurById   ($user   ->queteur_id);
      $ul      = $uniteLocaleDBService->getPointQueteById($queteur->ul_id     );

      $signer = new Sha256();

      $jwtSecret      = $this->get('settings')['jwt'        ]['secret'        ];
      $issuer         = $this->get('settings')['jwt'        ]['issuer'        ];
      $audience       = $this->get('settings')['jwt'        ]['audience'      ];
      $deploymentType = $this->get('settings')['appSettings']['deploymentType'];


      $token = (new Builder())
        ->setIssuer    ($issuer       ) // Configures the issuer (iss claim)
        ->setAudience  ($audience     ) // Configures the audience (aud claim)
        ->setIssuedAt  (time()        ) // Configures the time that the token was issue (iat claim)
        ->setNotBefore (time()        ) // Configures the time that the token can be used (nbf claim)
        ->setExpiration(time() + 4*3600 ) // Configures the expiration time of the token (nbf claim)
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
        ->getToken     ();                           // Retrieves the generated token

      $response->getBody()->write('{"token":"'.$token.'"}');

      $userDBService->registerSuccessfulLogin($user->id);

      return $response;
    }
    else if($user instanceof UserEntity)
    {//we found the user, but password is not good

      $this->logger->addError("Authentication failed, wrong password, for user id='".$user->id."' nivol='".$username."'");
      $userDBService->registerFailedLogin($user->id);
    }
    else
    {
      $this->logger->addError("Authentication failed, wrong password, for user nivol='".$username."', response is not a UserEntity : ".print_r($user,true));
    }

    $response401 = $response->withStatus(401);
    $response401->getBody()->write(json_encode("{error:'username or password error. Code 2'}"));

    return $response401;
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);

    $response401 = $response->withStatus(401);
    $response401->getBody()->write(json_encode("{error:'username or password error. Code 3.'}"));
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
  $username = trim($request->getParsedBodyParam("username"));
  $userDBService = new UserDBService   ($this->db, $this->logger);

  $uuid = $userDBService->sendInit($username);

  if($uuid != null)
  {
    $queteurDBService = new QueteurDBService($this->db, $this->logger);
    $queteur = $queteurDBService->getQueteurByNivol($username);
    //$this->logger->debug(print_r($queteur,true));
    $this->mailer->sendInitEmail($queteur, $uuid);

    $response->getBody()->write('{"success":true,"email":"'.$queteur->email.'"}');
    return $response;

  }
  else
  {//the user do not have an account
    $response->getBody()->write('{"success":false}');
    return $response;
  }
});




/**
 * Get user information from the UUID
 *
 */
$app->get('/getInfoFromUUID', function ($request, $response, $args) use ($app)
{
  $uuid = trim($request->getParam("uuid"));
  if(strlen($uuid) != 36)
  {
    $response->getBody()->write('{"success":false}');
    return $response;
  }
  $userDBService = new UserDBService   ($this->db, $this->logger);
  $user = $userDBService->getUserInfoWithUUID($uuid);


  if($uuid != null)
  {
    $queteurDBService = new QueteurDBService($this->db, $this->logger);
    $queteur = $queteurDBService->getQueteurById($user->queteur_id);

    $response->getBody()->write('{"success":true,"first_name":"'.$queteur->first_name.'","last_name":"'.$queteur->last_name.'","email":"'.$queteur->email.'","mobile":"'.$queteur->mobile.'","nivol":"'.$queteur->nivol.'"}');
    return $response;

  }
  else
  {//the user do not have an account
    $response->getBody()->write('{"success":false}');
    return $response;
  }
});


$app->post('/resetPassword', function ($request, $response, $args) use ($app)
{
  $uuid     = trim($request->getParsedBodyParam("uuid"));
  $password = trim($request->getParsedBodyParam("password"));

  $userDBService = new UserDBService   ($this->db, $this->logger);
  $user = $userDBService->getUserInfoWithUUID($uuid);

  if($user instanceof UserEntity)
  {
    $success = $userDBService->resetPassword($uuid, $password);

    if($success)
    {
      $queteurDBService = new QueteurDBService($this->db, $this->logger);
      $queteur = $queteurDBService->getQueteurById($user->queteur_id);

      $this->mailer->sendResetPasswordEmailConfirmation($queteur);

      $response->getBody()->write('{"success":true,"email":"'.$queteur->email.'"}');
      return $response;
    }
  }

  //the user do not have an account
  $response->getBody()->write('{"success":false}');
  return $response;

});
