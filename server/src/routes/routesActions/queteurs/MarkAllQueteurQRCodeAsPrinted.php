<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;


class MarkAllQueteurQRCodeAsPrinted extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private QueteurDBService $queteurDBService;

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
   * Marque (ou demarque) comme imprimes TOUS les QRCodes des queteurs de l'UL
   * de l'utilisateur connecte (ulId depuis le JWT). Pas de restriction sur la
   * liste affichee cote UI : l'UPDATE SQL porte sur l'ensemble de l'UL.
   *
   * C'est correct par design pour les deux boutons :
   *   - "Marquer tous les QRCode ci-dessous comme etant imprimes"
   *     La page filtre par defaut sur les QRCodes non imprimes => la liste
   *     affichee = ensemble des queteurs cibles. Si on filtre sur "imprimes",
   *     l'operation est idempotente. Cas degenere : un filtre par liste d'ids
   *     re-imprimerait aussi les autres queteurs - acceptable dans la pratique
   *     car la mise a jour reflete une realite materielle (impression de tous).
   *   - "Marquer tous les QRCode des Queteurs de l'UL comme etant NON imprimes"
   *     Le libelle est explicite sur le scope.
   *
   * A reconsiderer si une future feature change l'ensemble des queteurs
   * visibles sans cibler "tout l'UL" (ex: vue multi-UL, filtre serveur strict).
   *
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId    = $this->decodedToken->getUlId();
    $body    = $this->request->getParsedBody() ?? [];
    $printed = $body['printed'] ?? null;

    if (!is_bool($printed))
    {
      $error    = ['error' => "Invalid value for 'printed'. Expected boolean true or false in JSON body."];
      $response = $this->response->withStatus(400)->withHeader('Content-Type', 'application/json');
      $response->getBody()->write(json_encode($error));
      return $response;
    }

    $this->queteurDBService->markAllAsPrinted($ulId, $printed);
    return $this->response;
  }
}
