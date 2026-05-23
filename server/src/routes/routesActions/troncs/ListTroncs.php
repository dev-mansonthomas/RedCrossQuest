<?php




namespace RedCrossQuest\routes\routesActions\troncs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\TroncDBService;
use RedCrossQuest\Entity\PageableRequestEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;


class ListTroncs extends Action
{
  /**
   * @var TroncDBService          $troncDBService
   */
  private $troncDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param TroncDBService          $troncDBService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              TroncDBService          $troncDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->troncDBService = $troncDBService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    // Note sur le paramètre 'q' (préfixe de recherche par id de tronc) :
    // Le formulaire de recherche est alimenté par un scan QR code décodé côté JS
    // dans le navigateur (sans appel serveur). Les lecteurs QR peuvent produire des
    // chaînes numériques aberrantes en cas de mauvaise lecture (ex: q=76457645 alors
    // que max(tronc.id) ~= 7605). La borne max à 1 000 000 est volontairement très
    // au-dessus des id réels et sert de garde-fou : elle rejette le bruit du scanner
    // au lieu d'envoyer une requête SQL inutile. Les rejets logués en WARNING avec
    // actionClass=ListTroncs sont donc attendus et ne traduisent pas un bug.
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withInteger('pageNumber'  , $this->queryParams, 100     , false    ),
        ClientInputValidatorSpecs::withInteger('rowsPerPage' , $this->queryParams, 100     , false    ),
        ClientInputValidatorSpecs::withBoolean("active"      , $this->queryParams, false     , true),
        ClientInputValidatorSpecs::withInteger("type"        , $this->queryParams, 5       , false, null),
        ClientInputValidatorSpecs::withInteger("q"           , $this->queryParams, 1000000 , false, null),
      ]);

    $ulId   = $this->decodedToken  ->getUlId  ();

    $pageableRequest = new PageableRequestEntity($this->validatedData);

    $troncs = $this->troncDBService->getTroncs($pageableRequest, $ulId);

    $this->response->getBody()->write(json_encode($troncs));

    return $this->response;
  }
}
