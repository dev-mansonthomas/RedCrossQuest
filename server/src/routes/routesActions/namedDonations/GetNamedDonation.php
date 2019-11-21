<?php




namespace RedCrossQuest\routes\routesActions\namedDonations;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\NamedDonationDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class GetNamedDonation extends Action
{
  /**
   * @var NamedDonationDBService        $namedDonationDBService
   */
  private $namedDonationDBService;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param NamedDonationDBService        $namedDonationDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              NamedDonationDBService        $namedDonationDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->namedDonationDBService = $namedDonationDBService;
  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));

    $ulId   = $this->decodedToken->getUlId();
    $roleId = $this->decodedToken->getRoleId();

    $this->validateSentData([
      ClientInputValidatorSpecs::withInteger('id', $this->args['id'], 1000000, true)
    ]);

    $id     = $this->validatedData["id"];

    $namedDonationEntity = $this->namedDonationDBService->getNamedDonationById($id, $ulId, $roleId);

    $this->response->getBody()->write(json_encode($namedDonationEntity));

    return $this->response;
  }
}
