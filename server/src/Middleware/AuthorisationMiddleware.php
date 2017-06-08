<?php
// Application middleware
// e.g: $app->add(new \Slim\Csrf\Guard);

namespace RedCrossQuest\Middleware;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;


class AuthorisationMiddleware
{
  private static $errorMessage = [
    '0001' => "Rejecting non https (%s) connection on '%s'",
    '0002' => "Rejecting request as \$authorizations is empty (so no token)",
    '0003' => "Rejecting request, fails to decode Token or Authentication fails: %s",
    '0004' => "Rejecting request, failed to parse roleId from roleIdStr (%s') in Path: '%s' decoded token:%s",
    '0005' => "Rejecting request, retrieving the roleId : explode function fails to return 2 elements array as expected for Path: '%s', explodedPath: %s DecodedToken: %s",
    '0006' => "Rejecting request, roleId in Path is different from roleId in JWT Token: '%s', DecodedToken: %s",
    '0007' => "General Error while authenticating the request : %s",
    '0008' => "wrong format for a jwt token (should have exactly 2 '.')  '%s'",
    '0009' => "rejecting token, signature verification fails. Token : '%s'",
    '0010' => "JWT Validation fails: %s",
    '0011' => "Error while decoding the Token! Check that the Claims set during the authentication are the same than the one we're trying to get during the decode. %s"
  ];
  public function __construct($app)
  {
    //Define the urls that you want to exclude from Authentication, aka public urls
    $this->whiteList = array('\/authenticate');
    $this->logger = $app->getContainer()->get('logger');

    $this->bearer = "Bearer ";
    $this->bearerStrLen=strlen($this->bearer);

    $this->jwtSettings =  $app->getContainer()->get('settings')['jwt'];

    //$this->logger->addInfo("__construct finished");
  }


  /**
   * Check the validity of the JWT token
   *
   * @param string $tokenStr
   * @return DecodedToken
   * @throws \Exception exception
   */
  public function authenticate($tokenStr) : DecodedToken
  {
    //$this->logger->addDebug("Authentication check on :".print_r($tokenStr, true));
    
    if(substr_count($tokenStr, ".") !=  2)
    {
      $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0008'],$tokenStr));
      return new DecodedToken(false, '0008');
    }

    $token = (new Parser())->parse((string) $tokenStr);
    $signer = new Sha256();


    $jwtSecret = $this->jwtSettings['secret'  ];
    $issuer    = $this->jwtSettings['issuer'  ];
    $audience  = $this->jwtSettings['audience'];

    if(!$token->verify($signer, $jwtSecret))
    {
      $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0009'],$tokenStr));
      return new DecodedToken(false, '0009');
    }
    //$this->logger->addDebug("JWT Token not altered:".print_r($tokenStr, true));

    $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
    $data->setIssuer  ($issuer  );
    $data->setAudience($audience);

    $validation = $token->validate($data);
    $errorCode = '';

    if(!$validation)
    {
      $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0009'],print_r($tokenStr, true)));
      $errorCode = '0010';
    }
    else
    {
      //$this->logger->addInfo("JWT Validation OK:".print_r($tokenStr, true));
    }
    try
    {
      $decodedToken = DecodedToken::withData(
        $validation                        ,
        $errorCode                         ,
        $token->getClaim("username" ),
        $token->getClaim("id"       ),
        $token->getClaim("ulId"     ),
        $token->getClaim("ulName"   ),
        $token->getClaim("queteurId"),
        $token->getClaim("roleId"   )
      );

    }
    catch(Exception $error)
    { //getClaim can raise exception if the claim is not here
      $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0011'], print_r($error, true)));
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

      //check https for non localhost request
      if($scheme!="https" && $host != "localhost" )
      {//must be https except on localhost
        $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0001'], $scheme, $host));
        return $this->denyRequest($response, "0001");
      }

      //public path
      if($path == 'authenticate' || $path == 'sendInit' || $path == 'resetPassword' || $path == 'getInfoFromUUID')
      {
        return $next($request, $response);
      }

      $authorizations = $request->getHeader('Authorization');
      //get token
      if(count($authorizations) == 0)
      {
        $this->logger->addError(AuthorisationMiddleware::$errorMessage['0002']);
        return $this->denyRequest($response, '0002');
      }
      $authorization = $authorizations[0];
      $tokenStr = substr($authorization, $this->bearerStrLen, strlen($authorization) - $this->bearerStrLen);

      $decodedToken = $this->authenticate($tokenStr);

      //check if authenticated
      if(!($decodedToken instanceof DecodedToken) || $decodedToken->getAuthenticated() === false)
      {//token invalid
        $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0003'], print_r($decodedToken, true)));
        return $this->denyRequest($response, sprintf('0003'.'.%s',$decodedToken->getErrorCode()));
      }
      
      //check if the roleId in the URL match the one in the JWT Token (user might have changed the URL)

      $path = $request->getUri()->getPath();

      $explodedPath = explode("/", $path,2);

      if(count($explodedPath) == 2) //explode with limit 2 will return one element + the rest of the string, so array size is 2.
      {
        $roleIdStr = $explodedPath[0];

        if($roleIdStr == "1")
        {//intVal return 1 in case of error
          $roleId = 1;
        }
        else
        {
          $roleId = intVal($roleIdStr);
          if($roleId == 1)
          { //intVal return 1 when an error happen
            $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0004'], $roleIdStr, $path, print_r($decodedToken, true)));
            return $this->denyRequest($response, '0004');
          }
        }
      }
      else
      {
        $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0005'], $path, print_r($explodedPath, true), print_r($decodedToken, true)));
        return $this->denyRequest($response, '0005');
      }

      if($decodedToken->getRoleId() != $roleId)
      {
        $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0006'], $path, print_r($decodedToken, true)));
        return $this->denyRequest($response, '0006');
      }

      //add the decoded JWT Token so that it can be used by routes. (ex: spotfire requires userId, roleId, UlID from the user)
      $request = $request->withAttribute('decodedJWT', $decodedToken);

      //token valid
      return $next($request, $response);
    }
    catch(Exception $error)
    {
      $this->logger->addError(sprintf(AuthorisationMiddleware::$errorMessage['0007'], print_r($error, true)));
      return $this->denyRequest($response, '0007');
    }

  }


  private function denyRequest($response, $errorCode)
  {
    $response401 = $response->withStatus(401);
    $response401->getBody()->write(json_encode("{error:'authentication fails - $errorCode'}"));
    return $response401;
  }

}


class DecodedToken
{
  private $authenticated ;
  private $errorCode     ;
  private $username      ;
  private $uid           ;
  private $ulId          ;
  private $ulName        ;
  private $ulMode        ;
  private $queteurId     ;
  private $roleId        ;
  private $d             ;


  public function __construct($authenticated, $errorCode)
  {
    $this->authenticated = $authenticated;
    $this->errorCode     = $errorCode;


  }

  public static function withData($authenticated, $errorCode,
                                  $username, $uid, $ulId,
                                  $ulName, $ulMode, $queteurId,
                                  $roleId, $deploymentType)
  {
    $instance = new self($authenticated, $errorCode);

    $instance->setUsername   ($username         );
    $instance->setUid        (intval($uid)      );
    $instance->setUlId       (intval($ulId)     );
    $instance->setUlName     ($ulName           );
    $instance->setUlMode     (intval($ulMode   ));
    $instance->setQueteurId  (intVal($queteurId));
    $instance->setRoleId     (intval($roleId)   );
    $instance->setD          ($deploymentType   );


    return $instance;
  }

  /**
   * @return mixed
   */
  public function getUlName()
  {
    return $this->ulName;
  }

  /**
   * @param mixed $authenticated
   */
  public function setAuthenticated($authenticated)
  {
    $this->authenticated = $authenticated;
  }

  /**
   * @param mixed $errorCode
   */
  public function setErrorCode($errorCode)
  {
    $this->errorCode = $errorCode;
  }

  /**
   * @param mixed $username
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }

  /**
   * @param mixed $uid
   */
  public function setUid($uid)
  {
    $this->uid = $uid;
  }

  /**
   * @param mixed $ulId
   */
  public function setUlId($ulId)
  {
    $this->ulId = $ulId;
  }

  /**
   * @param mixed $queteurId
   */
  public function setQueteurId($queteurId)
  {
    $this->queteurId = $queteurId;
  }

  /**
   * @param mixed $roleId
   */
  public function setRoleId($roleId)
  {
    $this->roleId = $roleId;
  }

  /**
   * @param mixed $ulName
   */
  public function setUlName($ulName)
  {
    $this->ulName = $ulName;
  }

  /**
   * @return mixed
   */
  public function getErrorCode()
  {
    return $this->errorCode;
  }

  /**
   * @return int
   */
  public function getQueteurId(): int
  {
    return $this->queteurId;
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

  /**
   * @return mixed
   */
  public function getUlMode()
  {
    return $this->ulMode;
  }

  /**
   * @return mixed
   */
  public function getD()
  {
    return $this->d;
  }

  /**
   * @param mixed $ulMode
   */
  public function setUlMode($ulMode)
  {
    $this->ulMode = $ulMode;
  }

  /**
   * @param mixed $d
   */
  public function setD($d)
  {
    $this->d = $d;
  }
}


