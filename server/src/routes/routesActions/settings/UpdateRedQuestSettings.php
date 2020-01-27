<?php




namespace RedCrossQuest\routes\routesActions\settings;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\DBService\UniteLocaleSettingsDBService;
use RedCrossQuest\Entity\LoggingEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;


class UpdateRedQuestSettings extends Action
{

  /**
   * @var UniteLocaleSettingsDBService  $uniteLocaleSettingsDBService
   */
  private $uniteLocaleSettingsDBService;



  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UniteLocaleSettingsDBService  $uniteLocaleSettingsDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              UniteLocaleSettingsDBService  $uniteLocaleSettingsDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleSettingsDBService         = $uniteLocaleSettingsDBService;

  }

  /**
   * @return Response
   * @throws \Exception
   */
  protected function action(): Response
  {
    Logger::dataForLogging(new LoggingEntity($this->decodedToken));
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withBoolean("AutonomousDepartAndReturn"   , $this->parsedBody['applicationSettings']['redquest']['AutonomousDepartAndReturn'], true, false),
      ]);

    $AutonomousDepartAndReturn    = $this->validatedData["AutonomousDepartAndReturn"];

    $ulId   = $this->decodedToken->getUlId();

    //recupère les settings exitants
    $ulSettings = $this->uniteLocaleSettingsDBService ->getUniteLocaleById   ($ulId);

    if(isset($ulSettings['redquest']))
    {
      $ulSettings['redquest']['AutonomousDepartAndReturn']=$AutonomousDepartAndReturn;
    }
    else
    {
      $ulSettings['redquest']=[];
      $ulSettings['redquest']['AutonomousDepartAndReturn']=$AutonomousDepartAndReturn;
    }

    //TODO finish implementation with firestore

    return $this->response;
  }
}
