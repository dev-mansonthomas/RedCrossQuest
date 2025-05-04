<?php
// Application middleware
// e.g: $app->add(new \Slim\Csrf\Guard);

namespace RedCrossQuest\Middleware;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Google\ApiCore\ApiException;
use Lcobucci\JWT\Configuration;
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
 * @property mixed logger
 * @property string bearer
 * @property int bearerStrLen
 */
class AuthorisationMiddleware implements MiddlewareInterface
{
  private static array $errorMessage = [
    '0001' => "Rejecting non https (%s) connection on '%s'",
    '0002' => "Rejecting request as \$authorizations is empty (so no token)",
    '0003' => "Rejecting request, fails to decode Token or Authentication fails: %s, path: %s",
    '0004' => "Rejecting request, failed to parse roleId from roleIdStr (%s) in Path: '%s' decoded token:%s",
    '0005' => "Rejecting request, retrieving the roleId : explode function fails to return 2 elements array as expected for Path: '%s', explodedPath: %s DecodedToken: %s",
    '0006' => "Rejecting request, roleId in Path is different from roleId in JWT Token: Path: '%s', \$roleIdStr:'%s',  \$roleId:'%s', \$decodedToken->getRoleId():'%s', DecodedToken: %s",
    '0007' => "General Error while executing the request",
    '0008' => "wrong format for a jwt token (should have exactly 2 '.')  '%s'",
    '0009' => "rejecting token, Constraints verification fails. Token : '%s'",
    '0010' => "JWT Validation fails: %s",
    '0011' => "Error while decoding the Token! Check that the Claims set during the authentication are the same than the one we're trying to get during the decode.",
    '0012' => "General Error while executing the handle method of the handler",
  ];

  /** @var Configuration           $JWTConfiguration*/
  private Configuration           $JWTConfiguration;

  private LoggerInterface  $logger;
  private String $bearer = "Bearer ";
  private int $bearerStrLen = 0;
  /**
   * init of the constructor
   * @param Container $container the container
   * @throws DependencyException
   * @throws NotFoundException
   */
  public function __construct(Container $container)
  {
    $this->logger    = $container->get(LoggerInterface::class);

    $this->bearerStrLen=strlen($this->bearer);
    $this->JWTConfiguration = $container->get(Configuration::class);
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
      $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0008'], $tokenStr));
      return new DecodedToken( '0008');
    }

    $token       = $this->JWTConfiguration->parser()->parse((string) $tokenStr);
    $constraints = $this->JWTConfiguration->validationConstraints();

    if(!$this->JWTConfiguration->validator()->validate($token, ...$constraints))
    {
      //rerun the validations, but this time we get the failings validations for logging
      $violations = [];
      foreach($constraints as $constraint)
      {
        $this->JWTConfiguration->checkConstraint($constraint, $token , $violations);
      }
      
      $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0009'],$tokenStr), ["JWTViolations"=>$violations]);

      return new DecodedToken( '0009');
    }
    try
    {
      $decodedToken = DecodedToken::withData(
        true   ,
        ''       ,
        $token->claims()->get("username" ),
        $token->claims()->get("id"       ),
        $token->claims()->get("ulId"     ),
        $token->claims()->get("ulName"   ),
        $token->claims()->get("ulMode"   ),
        $token->claims()->get("queteurId"),
        $token->claims()->get("roleId"   ),
        $token->claims()->get("d"        )
      );

    }
    catch(Exception $error)
    { //getClaim can raise exception if the claim is not here
      $this->logger->error(AuthorisationMiddleware::$errorMessage['0011'], array(Logger::$EXCEPTION=>$error));
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
        $this->logger->warning(sprintf(AuthorisationMiddleware::$errorMessage['0001'], $scheme, $host));
        return $this->denyRequest("0001");
      }

      //$this->logger->debug("$path");
      //public path that must not go through authentication check
      if($path === '/rest/authenticate'                            ||
         $path === '/rest/firebase-authenticate'                   ||
         $path === '/rest/sendInit'                                ||
         $path === '/rest/resetPassword'                           ||
         $path === '/rest/getInfoFromUUID'                         ||
         $path === '/rest/ul_registration'                         ||
         $path === '/rest/ul_registration/check_registration_code' ||
         $path === '/rest/ul_registration/create_ul_in_lower_env'  ||
         $path === '/rest/html/management/dashboards'              ||
         str_starts_with($path, '/rest/thanks_mailing/')           ||
         str_starts_with($path, '/rest/redQuest/'))
      {
        $this->logger->info("Non authenticate route", array( "path"=>$path, "uuid"=>$uuid));
        return $handler->handle($request);
      }
      //$this->logger->debug("authenticated route : $path");
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
        $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0003'], json_encode($decodedToken), $path));
        return $this->denyRequest(sprintf('0003'.'.%s - %s', $decodedToken->getErrorCode(), $path));
      }
      Logger::dataForLogging(new LoggingEntity($decodedToken));
      
      //check if the roleId in the URL match the one in the JWT Token (user might have changed the URL)
      $path         = $request->getUri()->getPath();
      $explodedPath = explode("/",  substr($path, strlen('/rest/')),2);

      //$this->logger->debug("path info", array("path"=>$path, "explodedPath"=>$explodedPath, "\$_SERVER"=>$_SERVER));

      if(count($explodedPath) == 2) //explode with limit 2 will return one element + the rest of the string, so array size is maximum 2 but can be less
      {
        $roleIdStr = $explodedPath[0];

        if(!is_scalar($roleIdStr))
        {
          $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0004'], $roleIdStr, $path, json_encode($decodedToken)));
          return $this->denyRequest('0004');
        }

        $roleId = intval($roleIdStr);
      }
      else
      {
        $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0005'], $path, json_encode($explodedPath), json_encode($decodedToken)));
        return $this->denyRequest('0005');
      }

      if($decodedToken->getRoleId() != $roleId)
      {
        $this->logger->error(sprintf(AuthorisationMiddleware::$errorMessage['0006'], $path, $roleIdStr, $roleId, $decodedToken->getRoleId(), json_encode($decodedToken)));
        return $this->denyRequest('0006');
      }

      //add the decoded JWT Token so that it can be used by routes. (ex: spotfire requires userId, roleId, UlID from the user)
      $request = $request->withAttribute('decodedJWT', $decodedToken);

      //token valid
      //$start    = microtime(true);
      try
      {
        $response = $handler->handle($request);
      }
      catch(Exception $applicationError)
      {
        $this->logger->error(AuthorisationMiddleware::$errorMessage['0012'], array(Logger::$EXCEPTION=>$applicationError));
        return $this->denyRequest('0012', 500);
      }
      //log execution time in milliseconds; error(true/false);request path
      //$this->logger->debug("[PERF];".(microtime(true)-$start).";false;".$path);

      return $response;
    }
    catch(Exception $error)
    {
      //$this->logger->debug("[PERF];".(microtime(true)-$start).";true;".$path);
      $this->logger->error(AuthorisationMiddleware::$errorMessage['0007'], array(Logger::$EXCEPTION=>$error));
      return $this->denyRequest('0007');
    }

  }
  /**
   * @param string $errorCode the error code to send back to the frontend
   * @return ResponseInterface      the modified response
   */
  private function denyRequest(string $errorCode, int $status=401) : ResponseInterface
  {
    $response = new Response();
    $response401 = $response->withStatus($status);
    $response401->getBody()->write(json_encode("{error:'authentication fails - $errorCode'}"));
    return $response401;
  }

}
