<?php
// Application middleware
// e.g: $app->add(new \Slim\Csrf\Guard);

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;


class AuthorisationMiddleware
{
  
  public function __construct($app)
  {
    //Define the urls that you want to exclude from Authentication, aka public urls
    $this->whiteList = array('\/authenticate');
    $this->logger = $app->getContainer()->get('logger');

    $this->bearer = "Bearer ";
    $this->bearerStrLen=strlen($this->bearer);

    $this->jwtSettings =  $app->getContainer()->get('settings')['jwt'];

    $this->logger->addInfo("__construct finished");
  }


  /**
   * Check the validity of the JWT token
   *
   * @param string $tokenStr
   * @return bool
   */
  public function authenticate($tokenStr)
  {
    $this->logger->addInfo("Authentication check on :".print_r($tokenStr, true));

    if(substr_count($tokenStr, ".") !=  2)
    {
      $this->logger->addInfo("wrong format for a jwt token (should have exactly 2 '.')  '$tokenStr'");
      return new DecodedToken(false);
    }

    $token = (new Parser())->parse((string) $tokenStr);
    $signer = new Sha256();


    $jwtSecret = $this->jwtSettings['secret'  ];
    $issuer    = $this->jwtSettings['issuer'  ];
    $audience  = $this->jwtSettings['audience'];

    if(!$token->verify($signer, $jwtSecret))
    {
      $this->app->logger->addInfo("rejecting token, signature verification fails. Token : '$tokenStr'");
      return new DecodedToken(false);
    }
    $this->logger->addInfo("JWT Token not altered:".print_r($tokenStr, true));

    $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
    $data->setIssuer  ($issuer  );
    $data->setAudience($audience);

    $validation = $token->validate($data);
    if(!$validation)
    {
      $this->logger->addInfo("JWT Validation fails:".print_r($tokenStr, true));
    }
    else
    {
      $this->logger->addInfo("JWT Validation OK:".print_r($tokenStr, true));
    }
    try
    {
      $decodedToken = new DecodedToken(
        $validation,
        $token->getClaim("username"),
        $token->getClaim("id"),
        $token->getClaim("ulId"),
        $token->getClaim("queteurId"),
        $token->getClaim("roleId")
      );

    }
    catch(Exception $error)
    {
      $this->logger->addError("Error while decoding the Token! Check that the Claims set during the authentication are the same than the one we're trying to get during the decode");
      throw $error;
    }

    return $decodedToken;

  }


  /**
   * Example middleware invokable class
   *
   * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
   * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
   * @param  callable                                 $next     Next middleware
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function __invoke($request, $response, $next)
  {
    try
    {
      $path   = $request->getUri()->getPath   ();
      $scheme = $request->getUri()->getScheme ();
      $host   = $request->getUri()->getHost   ();


      if($scheme!="https" && $host != "localhost" )
      {//must be https except on localhost
        $this->logger->addInfo("Rejecting non https ($scheme) connection on '$host'");
        return $this->denyRequest($response);
      }

      //public path
      if($path == 'authenticate' || $path == 'sendInit' || $path == 'resetPwd')
      {
        return $next($request, $response);
      }
      $authorizations = $request->getHeader('Authorization');
      //get token
      if(count($authorizations) == 0)
      {
        $this->logger->addInfo("Rejecting request as \$authorizations is empty (so no token)");
        return $this->denyRequest($response);
      }
      $authorization = $authorizations[0];
      $tokenStr = substr($authorization, $this->bearerStrLen, strlen($authorization) - $this->bearerStrLen);

      $decodedToken = $this->authenticate($tokenStr);

      //check if authenticated
      if(!($decodedToken instanceof DecodedToken) || $decodedToken->getAuthenticated() === false)
      {//token invalid
        $this->logger->addInfo("Rejecting tokenStr:".print_r($tokenStr, true));
        return $this->denyRequest($response);
      }

      //check if authorized to access service
      $method = $request->getMethod();

      //token valid
      return $next($request, $response);
    }
    catch(Exception $error)
    {
      $this->logger->addError("Error while authenticating the request : " . print_r($error, true));
      return $this->denyRequest($response);
    }

  }


  private function denyRequest($response)
  {
    $response401 = $response->withStatus(401);
    $response401->getBody()->write(json_encode("{error:'authentication fails'}"));
    return $response401;
  }

}


class DecodedToken
{
  public function __construct($authenticated, $username, $uid, $ulId, $roleId)
  {
    $this->authenticated = $authenticated;

    $this->username      = $username;
    $this->uid           = intval($uid);
    $this->ulId          = intval($ulId);
    $this->roleId        = intval($roleId);
  }

  /**
   * @return mixed
   */
  public function getAuthenticated()
  {
    return $this->authenticated;
  }

  /**
   * @return mixed
   */
  public function getRoleId()
  {
    return $this->roleId;
  }

  /**
   * @return mixed
   */
  public function getUid()
  {
    return $this->uid;
  }

  /**
   * @return mixed
   */
  public function getUlId()
  {
    return $this->ulId;
  }

  /**
   * @return mixed
   */
  public function getUsername()
  {
    return $this->username;
  }
}

$app->add( new AuthorisationMiddleware($app) );
