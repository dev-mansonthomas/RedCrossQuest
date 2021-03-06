<?php




namespace RedCrossQuest\routes\routesActions\spotfire;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\SpotfireAccessDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class GetSpotfireAccessToken extends Action
{

  /**
   * @Inject("settings")
   * @var array settings
   */
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
    $ulId           = $this->decodedToken->getUlId  ();
    $userId         = $this->decodedToken->getUid();

    $validToken = $this->spotfireAccessDBService->getValidToken($userId, $ulId);

    if($validToken == null)
    {
      $validToken = $this->spotfireAccessDBService->grantAccess($userId ,$ulId,   $this->settings['appSettings']['sessionLength' ])->token;
    }

    $this->response->getBody()->write(json_encode(new GetSpotfireTokenResponse($validToken->token, $validToken->token_expiration)));

    return $this->response;
  }
}
