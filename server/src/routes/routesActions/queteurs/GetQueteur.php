<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class GetQueteur extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;

  /**
   * @var UserDBService $userDBService
   */
  private $userDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService          $queteurDBService
   * @param UserDBService $userDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService          $queteurDBService,
                              UserDBService $userDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService = $queteurDBService;
    $this->userDBService    = $userDBService   ;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId  ();
    $roleId = $this->decodedToken->getRoleId();
    $this->validateSentData([
      ClientInputValidatorSpecs::withInteger("id", $this->args['id'], 1000000 , false, 0)
    ]);

    $queteurId  = $this->validatedData["id"];
    $queteur    = $this->queteurDBService->getQueteurById($queteurId, $ulId);

    if($queteur->ul_id != $ulId && $roleId != 9)
    {
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'permission denied']));
      return $response401;
    }

    if($roleId >= 4)
    {//localAdmin & superAdmin
      $queteur->user = $this->userDBService->getUserInfoWithQueteurId($queteurId, $ulId, $roleId);
    }

    $this->response->getBody()->write(json_encode($queteur));
    return $this->response;
  }
}
