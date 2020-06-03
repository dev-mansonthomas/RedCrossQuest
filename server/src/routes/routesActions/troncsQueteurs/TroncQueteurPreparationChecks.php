<?php
namespace RedCrossQuest\routes\routesActions\troncsQueteurs;


use DI\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\TroncQueteurBusinessService;
use RedCrossQuest\DBService\TroncQueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class TroncQueteurPreparationChecks extends Action
{
  /**
   * @var TroncQueteurDBService          $troncQueteurDBService
   */
  private $troncQueteurDBService;

  /**
   * @var TroncQueteurBusinessService   $troncQueteurBusinessService
   */
  private $troncQueteurBusinessService;

  /**
   * @Inject("settings")
   * @var array settings
   */
  protected $settings;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncQueteurDBService $troncQueteurDBService
   * @param TroncQueteurBusinessService   $troncQueteurBusinessService
   */
  public function __construct(LoggerInterface             $logger,
                              ClientInputValidator        $clientInputValidator,
                              TroncQueteurDBService       $troncQueteurDBService,
                              TroncQueteurBusinessService $troncQueteurBusinessService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncQueteurDBService       = $troncQueteurDBService;
    $this->troncQueteurBusinessService = $troncQueteurBusinessService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId      = $this->decodedToken->getUlId       ();
    $userId    = $this->decodedToken->getUid        ();

    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('tronc_id'  , $this->queryParams, 1000000, true),
        ClientInputValidatorSpecs::withInteger('queteur_id', $this->queryParams, 1000000, true)
      ]);

    //c'est bien le troncId qu'on passe ici, on va supprimer tout les tronc_queteur qui ont ce tronc_id et départ ou retour à nulle
    $tronc_id   = $this->validatedData["tronc_id"];
    $queteur_id = $this->validatedData["queteur_id"];

    $troncQueteurs = $this->troncQueteurDBService->checkTroncNotAlreadyInUse($tronc_id, $queteur_id, $ulId);

    //in any case, we return the insert response
    $this->response->getBody()->write(json_encode(new PrepareTroncQueteurResponse($troncQueteurs!= null, $troncQueteurs)));
    return $this->response;
  }
}
