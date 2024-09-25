<?php




namespace RedCrossQuest\routes\routesActions\spotfire;


use DI\Attribute\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\SpotfireAccessDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use Throwable;


class GetSpotfireAccessToken extends Action
{

  /**
   * @var array settings
   */
  #[Inject("settings")]
  protected $settings;
  
  /**
   * @var SpotfireAccessDBService       $spotfireAccessDBService
   */
  private $spotfireAccessDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param SpotfireAccessDBService $spotfireAccessDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              SpotfireAccessDBService       $spotfireAccessDBService)
  {
    parent::__construct($logger, $clientInputValidator);

    $this->spotfireAccessDBService        = $spotfireAccessDBService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId           = $this->decodedToken->getUlId();
    $userId         = $this->decodedToken->getUid ();

    try
    {
      $validToken = $this->spotfireAccessDBService->grantAccess($userId ,$ulId,   $this->settings['appSettings']['sessionLength' ]);

      $this->response->getBody()->write(json_encode(new GetSpotfireTokenResponse($validToken->token, $validToken->token_expiration)));

      return $this->response;
    }
    catch(Throwable $exception)
    {
      $this->logger->error("Error while getting Spotfire access token",["decodedToken" => $this->decodedToken, "validToken"=>$validToken]);
      return $this->response->withStatus(500, "Error while getting Spotfire access token") ;
    }

  }
}
