<?php




namespace RedCrossQuest\routes\routesActions\queteurs;


use DI\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\PubSubService;


class AssociateRegistrationWithExistingQueteur extends Action
{
  /**
   * @var QueteurDBService          $queteurDBService
   */
  private $queteurDBService;

  /**
   * @var EmailBusinessService    $emailBusinessService
   */
  private $emailBusinessService;

  /**
   * @var PubSubService           $pubSubService
   */
  private $pubSubService;

  /**
   * @Inject("settings")
   * @var array settings
   */
  protected $settings;

  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param QueteurDBService          $queteurDBService
   * @param EmailBusinessService    $emailBusinessService
   * @param PubSubService           $pubSubService
   */
  public function __construct(LoggerInterface         $logger,
                              ClientInputValidator    $clientInputValidator,
                              QueteurDBService        $queteurDBService,
                              EmailBusinessService    $emailBusinessService,
                              PubSubService           $pubSubService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->queteurDBService     = $queteurDBService;
    $this->emailBusinessService = $emailBusinessService;
    $this->pubSubService        = $pubSubService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $ulId   = $this->decodedToken->getUlId  ();
    $userId = $this->decodedToken->getUid   ();

    $queteurEntity = new QueteurEntity($this->parsedBody, $this->logger);

    //restore the leading +
    $queteurEntity->mobile = "+".$queteurEntity->mobile;


    //validate the token, if validation fails, it throws an exception
    $tempArray = ['ul_registration_token'=>$queteurEntity->ul_registration_token];
    $this->validateSentData([
      ClientInputValidatorSpecs::withString("ul_registration_token", $tempArray, 36 , true, ClientInputValidator::$UUID_VALIDATION)
    ]);

    $queteurEntity->referent_volunteer = 0;

    //as we're associating the registration to an existing queteur, it's necessarily an approval
    $queteurEntity->registration_approved = true;
    $this->queteurDBService->associateRegistrationWithExistingQueteur($queteurEntity, $userId, $ulId);

    //publishing message to pubsub so that firebase is updated
    $responseMessageIds = null;
    $messageProperties  = [
      'ulId'          => "".$ulId,
      'uId'           => "".$userId,
      'queteurId'     => "".$queteurEntity->id,
      'registrationId'=> "".$queteurEntity->registration_id
    ];

    try
    {

      $this->emailBusinessService->sendRedQuestApprovalDecision($queteurEntity, $queteurEntity->registration_approved);

      $this->pubSubService->publish(
        $this->settings['PubSub']['queteur_approval_topic'],
        $queteurEntity,
        $messageProperties,
        true,
        true);
    }
    catch(Exception $exception)
    {
      $this->logger->error("error while publishing registration approval - associateRegistrationWithExistingQueteur",
        array("messageProperties"=> $messageProperties,
        "queteurEntity"    => $queteurEntity,
          Logger::$EXCEPTION        => $exception));
      //do not rethrow
    }

    $this->response->getBody()->write(json_encode( new AssociateRegistrationWithExistingQueteurResponse($queteurEntity->id)));
    return $this->response;
  }
}
