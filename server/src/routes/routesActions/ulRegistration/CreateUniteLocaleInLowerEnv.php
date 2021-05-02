<?php

namespace RedCrossQuest\routes\routesActions\ulRegistration;

use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\routes\routesActions\unitesLocales\ApproveULRegistrationResponse;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\ReCaptchaService;

class CreateUniteLocaleInLowerEnv extends Action
{

  /**
   * @var UniteLocaleDBService          $uniteLocaleDBService
   */
  private $uniteLocaleDBService;

  /**
   * @var ReCaptchaService              $reCaptchaService
   */
  private $reCaptchaService;

  /**
   * @var UserDBService                 $userDBService
   */
  private $userDBService;

  /**
   * @var EmailBusinessService          $emailBusinessService
   */
  private $emailBusinessService;

  /**
   * @var QueteurDBService              $queteurDBService
   */
  private QueteurDBService              $queteurDBService;

  /**
   * @param LoggerInterface       $logger
   * @param ClientInputValidator  $clientInputValidator
   * @param ReCaptchaService      $reCaptchaService
   * @param UserDBService         $userDBService
   * @param UniteLocaleDBService  $uniteLocaleDBService
   * @param EmailBusinessService  $emailBusinessService
   * @param QueteurDBService      $queteurDBService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              ReCaptchaService              $reCaptchaService,
                              UserDBService                 $userDBService,
                              UniteLocaleDBService          $uniteLocaleDBService,
                              EmailBusinessService          $emailBusinessService,
                              QueteurDBService              $queteurDBService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleDBService = $uniteLocaleDBService;
    $this->reCaptchaService     = $reCaptchaService;
    $this->userDBService        = $userDBService;
    $this->emailBusinessService = $emailBusinessService;
    $this->queteurDBService     = $queteurDBService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    if($this->emailBusinessService->getDeploymentEnvCode() ==='P')
    {//this method is forbidden in production
      $this->logger->error("create_ul_in_lower_env => access forbidden in production", ["request"=>$this->request]);
      $errorResponse = (new \Slim\Psr7\Response())->withStatus(500);
      $errorResponse->getBody()->write("");
      return $errorResponse;
    }
    
    $ulEntity = new UniteLocaleEntity($this->parsedBody['ul'], $this->logger);

    try
    {
      $ulEntity->registration_approved = true;
      $ulEntity->reject_reason         = "Automatically approved with token sent to president";

      $this->uniteLocaleDBService->transactionStart();
      
      //updating people details                         //we're operating outside of the current super admin UL
      $this->uniteLocaleDBService->updateUL($ulEntity, $ulEntity->id);
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
      $roleId=4;
      $queteurEntity = new QueteurEntity($queteurData, $this->logger);
      $queteurId     = $this->queteurDBService->insert($queteurEntity, $ulEntity->id, $roleId);
      $queteur       = $this->queteurDBService->getQueteurById($queteurId);

      $this->logger->debug("create_ul_in_lower_env, queteur inserted", ["queteur"=>$queteur]);
      //creating user
      $this->userDBService->insert($queteur->nivol, $queteur->id, 4);

      $this->logger->debug("create_ul_in_lower_env, user inserted", ["queteur"=>$queteur]);

      $user = $this->userDBService->getUserInfoWithQueteurId($queteur->id, $ulEntity->id, $roleId);
      //sending email with password init
      $uuid = $this->userDBService->sendInit                ($queteur->nivol, true);

      $this->uniteLocaleDBService->transactionCommit();
      //if emails fails, it's not a reason to rollback
      $this->emailBusinessService->sendInitEmail            ($queteur, $uuid, true);
      $this->emailBusinessService->sendULRegistrationApprovalDecision($ulEntity);







     /*
      * TODO:  send  a message over PubSub to update UL, Create Queteur, Activate user on the test env
      *
      $queteurSQL = $this->replaceDataInString($queteurEntity, QueteurDBService::$insertQueteurSQL);
      $ulSQL      = $this->replaceDataInString($ulEntity     , UniteLocaleDBService::$updateUniteLocaleSQL);

      $response = new ApproveULRegistrationResponse($user->id, $queteurId, $ulEntity->id, $ulEntity->registration_id, $queteurSQL, $ulSQL);
      $ulEntity->response = $response;
      $this->response->getBody()->write(json_encode($ulEntity));
     */
    }
    catch (Exception $e)
    {
      $this->logger->error("Error while approving a UL Registration",["ulRegistration"=>$ulEntity, Logger::$EXCEPTION=>$e]);
      $this->uniteLocaleDBService->transactionRollback();
      $errorResponse = (new \Slim\Psr7\Response())->withStatus(500);
      $errorResponse->getBody()->write(json_encode($e));
      return $errorResponse;
    }


    $this->response->getBody()->write(json_encode(["success"=>true]));
    return $this->response;




    



  }
}
