<?php
/**
 * Created by IntelliJ IDEA.
 * User: tmanson
 * Date: 06/03/2017
 * Time: 18:36
 */

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/********************************* Authentication ****************************************/

$app->post('/authenticate', function ($request, $response, $args) use ($app)
{
  $jwtSecret = $this->get('settings')['jwt']['secret'  ];
  $issuer    = $this->get('settings')['jwt']['issuer'  ];
  $audience  = $this->get('settings')['jwt']['audience'];

  $userMapper    = new RedCrossQuest\UserMapper   ($this->db, $this->logger);
  $queteurMapper = new RedCrossQuest\QueteurMapper($this->db, $this->logger);

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
      $response201->getBody()->write(json_encode("{error:'username or password error'}"));

      return $response201;
    }


    $this->logger->addInfo("attempting to login with username='$username' and password size='".strlen($password)."'");

    $user = $userMapper->getUserInfoWithNivol($username);

    if($user instanceof \RedCrossQuest\UserEntity &&
      password_verify($password, $user->password))
    {
      $queteur = $queteurMapper->getQueteurById($user->queteur_id);



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
    $response201->getBody()->write(json_encode("{body:'username or password error'}"));

    return $response201;
  }
  catch(Exception $e)
  {
    $this->logger->addError($e);
    throw $e;
  }
});

