<?php
// Application middleware
// e.g: $app->add(new \Slim\Csrf\Guard);

namespace RedCrossQuest\Middleware;

require '../../vendor/autoload.php';

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\Service\Logger;
use Slim\Psr7\Response;

/**
 * @property array whiteList
 * @property mixed logger
 * @property string bearer
 * @property int bearerStrLen
 */
class AuthorisationMiddleware implements MiddlewareInterface
{
  private static $errorMessage = [
    '0001' => "Rejecting non https (%s) connection on '%s'",
    '0002' => "Rejecting request as \$authorizations is empty (so no token)",
    '0003' => "Rejecting request, fails to decode Token or Authentication fails: %s, path: %s",
    '0004' => "Rejecting request, failed to parse roleId from roleIdStr (%s) in Path: '%s' decoded token:%s",
    '0005' => "Rejecting request, retrieving the roleId : explode function fails to return 2 elements array as expected for Path: '%s', explodedPath: %s DecodedToken: %s",
    '0006' => "Rejecting request, roleId in Path is different from roleId in JWT Token: Path: '%s', \$roleIdStr:'%s',  \$roleId:'%s', \$decodedToken->getRoleId():'%s', DecodedToken: %s",
    '0007' => "General Error while executing the request",
    '0008' => "wrong format for a jwt token (should have exactly 2 '.')  '%s'",
    '0009' => "rejecting token, signature verification fails. Token : '%s'",
    '0010' => "JWT Validation fails: %s",
    '0011' => "Error while decoding the Token! Check that the Claims set during the authentication are the same than the one we're trying to get during the decode."
  ];

  private $jwtSettings;

  /**
   * init of the constructor
   * @param Container $container the container
   * @throws DependencyException
   * @throws NotFoundException
   */
  public function __construct(Container $container)
  {
    //Define the urls that you want to exclude from Authentication, aka public urls
    $this->whiteList = array('\/authenticate');
    $this->logger    = $container->get(LoggerInterface::class );

    $this->bearer      = "Bearer ";
    $this->bearerStrLen=strlen($this->bearer);

    $this->jwtSettings =  $container->get('settings')['jwt'];

    //$this->logger->info("__construct finished");
  }


  /**
   * Check the validity of the JWT token
   *
   * @param string $tokenStr
   * @return DecodedToken
   * @throws Exception exception
   */
  public function authenticate(string $tokenStr) : DecodedToken
  {
    //$this->logger->debug("Authentication check on :".print_r($tokenStr, true));
    
    if(substr_count($tokenStr, ".") !=  2)
    {
      $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0008'],print_r($tokenStr, true)));
      return new DecodedToken( '0008');
    }

    $token = (new Parser())->parse((string) $tokenStr);
    $signer = new Sha256();


    $jwtSecret = $this->jwtSettings['secret'  ];
    $issuer    = $this->jwtSettings['issuer'  ];
    $audience  = $this->jwtSettings['audience'];

    if(!$token->verify($signer, $jwtSecret))
    {
      $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0009'],print_r($tokenStr, true)));
      return new DecodedToken( '0009');
    }
    //$this->logger->debug("JWT Token not altered:".print_r($tokenStr, true));

    $data = new ValidationData (); // It will use the current time to validate (iat, nbf and exp)
    $data->setIssuer  ($issuer  );
    $data->setAudience($audience);

    $validation = $token->validate($data);
    $errorCode  = '';

    if(!$validation)
    {
      $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0009'],print_r($tokenStr, true)));
      $errorCode = '0010';
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
        $token->getClaim("ulMode"   ),
        $token->getClaim("queteurId"),
        $token->getClaim("roleId"   ),
        $token->getClaim("d"        )
      );

    }
    catch(Exception $error)
    { //getClaim can raise exception if the claim is not here
      $this->logger->error(AuthorisationMiddleware::$errorMessage['0011'], array("exception"=>$error));
      throw $error;
    }

    return $decodedToken;

  }


  /**
   *
   *
   * @param  ServerRequestInterface  $request PSR-7 request
   * @param  RequestHandlerInterface $handler PSR-15 request handler
   *
   * @return ResponseInterface
   *
   * @throws Exception
   */
  //public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)

  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
  {
    $path  = "";
    $start =  0;
    $uuid  = Uuid::uuid4();
    try
    {
      $path   = $request->getUri()->getPath   ();
      //$this->logger->error($path, array("\$_SERVER"=>$_SERVER));
      $scheme = $request->getUri()->getScheme ();
      $host   = $request->getUri()->getHost   ();

      //check https for non localhost request
      if($scheme!="https" && $host != "localhost" && $host != "127.0.0.1" && $host != "rcq" )
      {//must be https except on localhost
        $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0001'], $scheme, $host));
        return $this->denyRequest("0001");
      }

      //$this->logger->error("$path");
      //$this->logger->error("getPrefix(true)='".getPrefix(true)."'");
      //$this->logger->error("isGAE()='".isGAE()."'");
      //public path that must not go through authentication check
      if($path == getPrefix(true).'/rest/authenticate'      ||
         $path == getPrefix(true).'/rest/firebase-authenticate'      ||
         $path == getPrefix(true).'/rest/sendInit'          ||
         $path == getPrefix(true).'/rest/resetPassword'     ||
         $path == getPrefix(true).'/rest/getInfoFromUUID'   ||
         strpos($path,getPrefix(true).'/rest/thanks_mailing/') === 0 ||
         strpos($path,getPrefix(true).'/rest/redQuest/'      ) === 0   )
      {
        $this->logger->info("Non authenticate route", array("prefix"=>getPrefix(true), "path"=>$path, "uuid"=>$uuid));
        return $handler->handle($request);
      }
      //$this->logger->error("authenticated route : $path");
      $authorizations = $request->getHeader('Authorization');
      //get token
      if(count($authorizations) == 0)
      {
        $this->logger->error(AuthorisationMiddleware::$errorMessage['0002']);
        return $this->denyRequest('0002');
      }
      $authorization = $authorizations[0];
      $tokenStr      = substr($authorization, $this->bearerStrLen, strlen($authorization) - $this->bearerStrLen);

      $decodedToken = $this->authenticate($tokenStr);

      //check if authenticated
      if($decodedToken->getAuthenticated() === false)
      {//token invalid
        $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0003'], print_r($decodedToken, true), $path));
        return $this->denyRequest(sprintf('0003'.'.%s - %s', $decodedToken->getErrorCode(), $path));
      }
      Logger::dataForLogging(new LoggingEntity($decodedToken));
      
      //check if the roleId in the URL match the one in the JWT Token (user might have changed the URL)
      $path         = $request->getUri()->getPath();
      $explodedPath = explode("/",  substr($path, strlen('/rest/')),2); //isGAE() ? substr($path, strlen('/rest/')):$path

      //$this->logger->error("path info", array("path"=>$path, "explodedPath"=>$explodedPath, "\$_SERVER"=>$_SERVER));

      if(count($explodedPath) == 2) //explode with limit 2 will return one element + the rest of the string, so array size is maximum 2 but can be less
      {
        $roleIdStr = $explodedPath[0];

        if(!is_scalar($roleIdStr))
        {
          $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0004'], $roleIdStr, $path, print_r($decodedToken, true)));
          return $this->denyRequest('0004');
        }

        $roleId = intval($roleIdStr);
      }
      else
      {
        $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0005'], $path, print_r($explodedPath, true), print_r($decodedToken, true)));
        return $this->denyRequest('0005');
      }

      if($decodedToken->getRoleId() != $roleId)
      {
        $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0006'], $path, $roleIdStr, $roleId, $decodedToken->getRoleId(), print_r($decodedToken, true)));
        return $this->denyRequest('0006');
      }

      //add the decoded JWT Token so that it can be used by routes. (ex: spotfire requires userId, roleId, UlID from the user)
      $request = $request->withAttribute('decodedJWT', $decodedToken);

      //token valid
      $start    = microtime(true);
      try
      {
        $response = $handler->handle($request);
      }
      catch(Exception $applicationError)
      {
        $this->logger->error(AuthorisationMiddleware::$errorMessage['0007'], array("exception"=>$applicationError));

        return (new Response())->withStatus(500);
      }
      //log execution time in milliseconds; error(true/false);request path
      $this->logger->debug("[PERF];".(microtime(true)-$start).";false;".$path);

      return $response;
    }
    catch(Exception $error)
    {
      $this->logger->debug("[PERF];".(microtime(true)-$start).";true;".$path);
      $this->logger->error(AuthorisationMiddleware::$errorMessage['0007'], array("exception"=>$error));
      return $this->denyRequest('0007');
    }

  }
  /**
   * @param string $errorCode the error code to send back to the frontend
   * @return ResponseInterface      the modified response
   */
  private function denyRequest($errorCode) : ResponseInterface
  {
    $response = new Response();
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


  /**
   * Init teh DecodedToken as an authentication failure (this->authenticated=false) with an error code
   * @param string $errorCode  the error code associated with the failed authentication request
   */
  public function __construct($errorCode)
  {
    $this->authenticated = false;
    $this->errorCode     = $errorCode;
  }

  /**
   * init the DecodedToken with data decoded from the token... ;)
   * @param boolean $authenticated true if the token is considered as valid, false otherwise
   * @param string $errorCode the string representing the error code '0004' for ex.
   * @param string $username the nivol of the user
   * @param string $uid the id of the user
   * @param string $ulId the id of the unite local to which the user belong to
   * @param string $ulName the name of the unite locale to which the user belong to
   * @param string $ulMode How the UniteLocal manage the quete (like Paris, like Marseille, like small city)
   * @param string $queteurId the id in the queteur table of the user
   * @param string $roleId the role id of the user (what can he do in the application)
   * @param string $deploymentType How the application is currently deployed: test/uat/production
   * @return DecodedToken an instance
   */
  public static function withData(bool    $authenticated, string $errorCode,
                                  string  $username     , string $uid      , string $ulId     ,
                                  string  $ulName       , string $ulMode   , string $queteurId,
                                  string  $roleId       , string $deploymentType)
  {
    $instance = new self($errorCode);

    $instance->setAuthenticated ($authenticated    );
    $instance->setUsername      ($username         );
    $instance->setUid           (intval($uid)      );
    $instance->setUlId          (intval($ulId)     );
    $instance->setUlName        ($ulName           );
    $instance->setUlMode        (intval($ulMode   ));
    $instance->setQueteurId     (intVal($queteurId));
    $instance->setRoleId        (intval($roleId)   );
    $instance->setD             ($deploymentType   );


    return $instance;
  }

  public function __toString()
  {
    return json_encode($this->toArray());
  }

  public function toArray()
  {
    return [  "authenticated" => $this->authenticated ,
      "errorCode"     => $this->errorCode     ,
      "username"      => $this->username      ,
      "uid"           => $this->uid           ,
      "ulId"          => $this->ulId          ,
      "ulName"        => $this->ulName        ,
      "ulMode"        => $this->ulMode        ,
      "queteurId"     => $this->queteurId     ,
      "roleId"        => $this->roleId        ,
      "d"             => $this->d
    ];
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

