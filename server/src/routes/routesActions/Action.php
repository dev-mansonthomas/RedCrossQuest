<?php


namespace RedCrossQuest\routes\routesActions;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use RedCrossQuest\Middleware\DecodedToken;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use Slim\Exception\HttpInternalServerErrorException;


abstract class Action
{

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @var ClientInputValidator
   */
  protected $clientInputValidator;

  /**
   * @var Request
   */
  protected $request;

  /**
   * @var Response
   */
  protected $response;

  /**
   * @var array
   */
  protected $args;

  /**
   * @var array
   */
  protected $queryParams;

  /**
   * @var array
   */
  protected $parsedBody;


  /**
   * @var array associative array of inputName=>inputValue
   */
  protected $validatedData;

  /**
   * @var DecodedToken
   */
  protected $decodedToken;
  

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   */
  public function __construct(LoggerInterface $logger, ClientInputValidator $clientInputValidator)
  {
    $this->logger               = $logger;
    $this->clientInputValidator = $clientInputValidator;
  }


  /**
   * @param Request  $request
   * @param Response $response
   * @param array    $args
   * @return Response
   * @throws Exception
   */
  public function __invoke(Request $request, Response $response, $args): Response
  {
    $this->request      = $request;
    $this->response     = $response;
    $this->args         = $args;
    $this->decodedToken = $request->getAttribute('decodedJWT');
    $this->queryParams  = $request->getQueryParams();
    $this->parsedBody   = $request->getParsedBody();

    try
    {
      return $this->action();
    }
    catch (Exception $e)
    {
      //protect password from being dumped in the logs.
      $validatedData = $this->validatedData;
      if( $validatedData !== null && key_exists("password", $validatedData))
      {
        $validatedData["password"] = strlen($validatedData["password"]);
      }
      $this->logger->error("Uncaught exception on Action", ["actionClass"=>get_class($this), "validatedData"=>$validatedData, "exception"=>$e]);
      throw new HttpInternalServerErrorException($this->request, $e->getMessage(), $e);
    }
  }


  /**
   * @return Response
   * @throws Exception
   */
  abstract protected function action(): Response;

  /**
   * initialize associative array $this->validatedData  as  parameterName=>parameterValue
   * The __invoke method will catch any exception, log the exception, class name and those values
   * @param ClientInputValidatorSpecs[] $clientInputValidatorInputs
   *
   */
  protected function validateSentData(array $clientInputValidatorInputs)
  {
    $this->validatedData = [];
    
    foreach($clientInputValidatorInputs as $clientInputValidatorInput)
    {
      $this->validatedData[$clientInputValidatorInput->parameterName] = $this->clientInputValidator->validate($clientInputValidatorInput);
    }
  }

  /**
   * In $this->queryParams, return the value associated to the $key, null if not found
   * @param string $key    the searched key
   * @return string the value or null if not found.
   */
  protected function getParam(string $key)
  {
    if(array_key_exists($key, $this->queryParams))
      return $this->queryParams[$key];
    return null;
  }
}
