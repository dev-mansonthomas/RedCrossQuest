<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class GetQueteurRegistration extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService          $queteurDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService          $queteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService = $queteurDBService;

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
      ClientInputValidatorSpecs::withInteger("id", $this->args, 1000000 , false, 0)
    ]);

    $queteurId  = $this->validatedData["id"];

    $queteur    = $this->queteurDBService->getQueteurRegistration($ulId, $queteurId);

    if($queteur->ul_id != $ulId && $roleId != 9)
    {
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error"=>'permission denied']));
      return $response401;
    }

    //so that it's preset to active. No point of accepting a registration of an inactive queteur
    $queteur->active = true;
    //unset the decision to not pre select any answer
    unset($queteur->registration_approved);
    $this->response->getBody()->write(json_encode($queteur));
    return $this->response;
  }
}
