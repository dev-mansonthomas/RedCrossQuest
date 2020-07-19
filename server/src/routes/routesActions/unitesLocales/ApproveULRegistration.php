<?php




namespace RedCrossQuest\routes\routesActions\unitesLocales;


use Carbon\Carbon;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\Entity;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\routes\routesActions\settings\ApproveULRegistrationResponse;
use RedCrossQuest\Service\ClientInputValidator;


class ApproveULRegistration extends Action
{

  /**
   * @var UniteLocaleDBService          $uniteLocaleDBService
   */
  private $uniteLocaleDBService;

  /**
   * @var QueteurDBService              $queteurDBService
   */
  private $queteurDBService;

  /**
   * @var UserDBService                 $userDBService
   */
  private $userDBService;

  /**
   * @var EmailBusinessService          $emailBusinessService
   */
  private $emailBusinessService;


  /**
   * @param LoggerInterface $logger
   * @param ClientInputValidator $clientInputValidator
   * @param UniteLocaleDBService $uniteLocaleDBService
   * @param QueteurDBService $queteurDBService
   * @param UserDBService $userDBService
   * @param EmailBusinessService $emailBusinessService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              UniteLocaleDBService          $uniteLocaleDBService,
                              QueteurDBService              $queteurDBService,
                              UserDBService                 $userDBService,
                              EmailBusinessService          $emailBusinessService
)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleDBService = $uniteLocaleDBService;
    $this->queteurDBService     = $queteurDBService;
    $this->userDBService        = $userDBService;
    $this->emailBusinessService = $emailBusinessService;

  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $userId = $this->decodedToken->getUid   ();
    $roleId = $this->decodedToken->getRoleId();

    $ulEntity = new UniteLocaleEntity($this->parsedBody, $this->logger);

    $originalUlRegistration = $this->uniteLocaleDBService->getULRegistration($ulEntity->registration_id);

    if($originalUlRegistration->id != $ulEntity->id)
    {
      $this->logger->error("UL_ID changed between registration and approval");
      throw new Exception("UL_ID changed between registration and approval");
    }

    if($ulEntity->registration_approved===false)
    {
      $this->uniteLocaleDBService->updateULRegistration($ulEntity);

      $this->emailBusinessService->sendULRegistrationApprovalDecision($ulEntity);
      $this->response->getBody()->write(json_encode($ulEntity));
      return  $this->response;
    }

    try
    {
      $this->uniteLocaleDBService->transactionStart();


      $this->uniteLocaleDBService->updateULRegistration($ulEntity);
      //updating people details                         //we're operating outside of the current super admin UL
      $this->uniteLocaleDBService->updateUL($ulEntity, $ulEntity->id, $userId);
      $this->uniteLocaleDBService->updateULDateDemarrageRCQ($ulEntity->id);
      
      //creating queteur
      $queteurData = [
        'email'       => $ulEntity->admin_email       ,
        'first_name'  => $ulEntity->admin_first_name  ,
        'last_name'   => $ulEntity->admin_last_name   ,
        'secteur'     => 1                            ,
        'nivol'       => $ulEntity->admin_nivol       ,
        'mobile'      => "+".$ulEntity->admin_mobile  ,
        'active'      => 1                            ,
        'man'         => $ulEntity->admin_man         ,
        'birthdate'   => '1922-12-22'                 ,
        'ul_id'       => $ulEntity->id
      ];

      $queteurEntity = new QueteurEntity($queteurData, $this->logger);
      $queteurId     = $this->queteurDBService->insert($queteurEntity, $ulEntity->id, $roleId);
      $queteur       = $this->queteurDBService->getQueteurById($queteurId);
      //creating user
      $this->userDBService->insert($queteur->nivol, $queteur->id, 4);
      $user = $this->userDBService->getUserInfoWithQueteurId($queteur->id, $ulEntity->id, $roleId);
      //sending email with password init
      $uuid = $this->userDBService->sendInit                ($queteur->nivol, true);

      $this->uniteLocaleDBService->transactionCommit();
      //if emails fails, it's not a reason to rollback
      $this->emailBusinessService->sendInitEmail            ($queteur, $uuid, true);
      $this->emailBusinessService->sendULRegistrationApprovalDecision($ulEntity);


      $queteurSQL = $this->replaceDataInString($queteurEntity, QueteurDBService::$insertQueteurSQL);
      $ulSQL      = $this->replaceDataInString($ulEntity     , UniteLocaleDBService::$updateUniteLocaleSQL);

      $response = new ApproveULRegistrationResponse($user->id, $queteurId, $ulEntity->id, $ulEntity->registration_id, $queteurSQL, $ulSQL);
      $ulEntity->response = $response;
      $this->response->getBody()->write(json_encode($ulEntity));

    }
    catch (Exception $e)
    {
      $this->logger->error("Error while approving a UL Registration",["ulRegistration"=>$ulEntity, "exception"=>$e]);
      $this->uniteLocaleDBService->transactionRollback();
      $errorResponse = (new \Slim\Psr7\Response())->withStatus(500);
      $errorResponse->getBody()->write(json_encode($e));
      return $errorResponse;
    }


    return $this->response;
  }


  private function replaceDataInString(Entity $entity, string $sql):string
  {
    $fieldList = $entity->getFieldList();
    foreach($fieldList as $fieldName)
    {
      $varType = gettype($entity->$fieldName);
      if($varType == 'boolean')
        $value = $entity->$fieldName ? 1:0;
      else if($varType == 'integer')
        $value = $entity->$fieldName;
      else
        $value = '"'.$entity->$fieldName.'"' ;

      if($fieldName == 'referent_volunteer')
      {
        $value = 0;
      }
      
      $sql = str_replace(":$fieldName", $value, $sql);
    }
    return $sql.";";
  }
}
