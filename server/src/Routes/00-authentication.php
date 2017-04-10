<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:36
 */

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

use \RedCrossQuest\DBService\UserDBService;
use \RedCrossQuest\DBService\QueteurDBService;

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
  $jwtSecret = $this->get('settings')['jwt']['secret'  ];
  $issuer    = $this->get('settings')['jwt']['issuer'  ];
  $audience  = $this->get('settings')['jwt']['audience'];

  $userDBService    = new UserDBService   ($this->db, $this->logger);
  $queteurDBService = new QueteurDBService($this->db, $this->logger);

  try
  {

    $username = trim($request->getParsedBodyParam("username"));
    $password = trim($request->getParsedBodyParam("password"));

    //refusing null, empty, big
    if($username == null || $password == null ||
      strlen($username) == 0 || strlen($password) == 0 ||
      strlen($username) > 10 || strlen($password) > 20)
    {
      $response201 = $response->withStatus(201);
      $response201->getBody()->write(json_encode("{error:'username or password error. Code 1'}"));

      return $response201;
    }


    $this->logger->addInfo("attempting to login with username='$username' and password size='".strlen($password)."'");

    $user = $userDBService->getUserInfoWithNivol($username);

    if($user instanceof UserEntity &&
      password_verify($password, $user->password))
    {
      $queteur = $queteurDBService->getQueteurById($user->queteur_id);

      $signer = new Sha256();
      
      $token = (new Builder())
        ->setIssuer    ($issuer       ) // Configures the issuer (iss claim)
        ->setAudience  ($audience     ) // Configures the audience (aud claim)
        ->setIssuedAt  (time()        ) // Configures the time that the token was issue (iat claim)
        ->setNotBefore (time()        ) // Configures the time that the token can be used (nbf claim)
        ->setExpiration(time() + 3600 ) // Configures the expiration time of the token (nbf claim)
        //Business Payload
        ->set          ('username' , $username      )
        ->set          ('id'       , $user->id      )
        ->set          ('ulId'     , $queteur->ul_id)
        ->set          ('queteurId', $queteur->id   )
        ->set          ('roleId'   , $user->role    )

        ->sign         ($signer    , $jwtSecret     ) // Sign the token
        ->getToken     ();                           // Retrieves the generated token

      $response->getBody()->write('{"token":"'.$token.'"}');
      
      return $response;
    }

    $response201 = $response->withStatus(201);
    $response201->getBody()->write(json_encode("{error:'username or password error. Code 2'}"));

    return $response201;
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);

    $response201 = $response->withStatus(201);
    $response201->getBody()->write(json_encode("{error:'username or password error. Code 3.'}"));
    return $response201;
  }
});

