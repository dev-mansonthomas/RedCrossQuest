<?php

namespace RedCrossQuest\routes\routesActions\ulRegistration;

use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\QueteurDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\ReCaptchaService;
use RedCrossQuest\Service\RedCallService;

class ValidateULRegistration extends Action
{

  /**
   * @var UniteLocaleDBService    $uniteLocaleDBService
   */
  private UniteLocaleDBService    $uniteLocaleDBService;

  /**
   * @var ReCaptchaService        $reCaptchaService
   */
  private ReCaptchaService        $reCaptchaService;

  /**
   * @var UserDBService           $userDBService
   */
  private UserDBService           $userDBService;

  /**
   * @var EmailBusinessService    $emailBusinessService
   */
  private EmailBusinessService    $emailBusinessService;

  /**
   * @var RedCallService          $redCallService
   */
  private RedCallService          $redCallService;

  /**
   * @var QueteurDBService        $queteurDBService
   */
  private QueteurDBService        $queteurDBService;


  /**
   * @param LoggerInterface       $logger
   * @param ClientInputValidator  $clientInputValidator
   * @param ReCaptchaService      $reCaptchaService
   * @param UserDBService         $userDBService
   * @param UniteLocaleDBService  $uniteLocaleDBService
   * @param EmailBusinessService  $emailBusinessService
   * @param QueteurDBService      $queteurDBService
   * @param RedCallService        $redCallService
   */
  public function __construct(LoggerInterface               $logger,
                              ClientInputValidator          $clientInputValidator,
                              ReCaptchaService              $reCaptchaService,
                              UserDBService                 $userDBService,
                              UniteLocaleDBService          $uniteLocaleDBService,
                              EmailBusinessService          $emailBusinessService,
                              QueteurDBService              $queteurDBService,
                              RedCallService                $redCallService
  )
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleDBService = $uniteLocaleDBService;
    $this->reCaptchaService     = $reCaptchaService;
    $this->userDBService        = $userDBService;
    $this->emailBusinessService = $emailBusinessService;
    $this->queteurDBService     = $queteurDBService;
    $this->redCallService       = $redCallService;
  }

  /**
   * @return Response
   * @throws Exception
   */
  protected function action(): Response
  {
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString("token"   , $this->parsedBody, 1500 , true),
      ]);

    $token    = $this->validatedData["token"   ];

    $reCaptchaResponseCode = $this->reCaptchaService->verify($token, "rcq/validateULRegistration", "ValidateULRegistration");

    if($reCaptchaResponseCode > 0)
    {// error
      $this->logger->error("validateULRegistration: ReCaptcha error ", array('token' => $token, 'ReCode'=>$reCaptchaResponseCode));

      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"An error occurred - ReCode $reCaptchaResponseCode"]));

      return $response401;
    }
    
    $this->validateSentData(
      [
        ClientInputValidatorSpecs::withString ("registration_token", $this->parsedBody, 36  , true),
        ClientInputValidatorSpecs::withInteger("ul_id"             , $this->parsedBody, 1000 , true),
        ClientInputValidatorSpecs::withInteger("registration_id"   , $this->parsedBody, 1000 , true)
      ]);


    $rowCount = $this->uniteLocaleDBService->validateULRegistration(
      $this->validatedData['registration_token'],
      $this->validatedData['ul_id'             ],
      $this->validatedData['registration_id'   ]
    );

    if($rowCount === 1)
    { // it means that the token is the one expected, so that the president did forward the token to the administrator
      //So the registration is approved
      try
      {
        $ulEntity = $this->uniteLocaleDBService->getULRegistration($this->validatedData['registration_id'  ]);

        $ulEntity->registration_approved = true;
        $ulEntity->reject_reason         = "Automatically approved with token sent to president";

        $this->uniteLocaleDBService->transactionStart();

        $this->uniteLocaleDBService->updateULRegistration($ulEntity);
        //updating people details                         //we're operating outside of the current super admin UL
        $this->uniteLocaleDBService->updateUL                ($ulEntity, $ulEntity->id);
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
        $queteurId     = $this->queteurDBService->insert        ($queteurEntity, $ulEntity->id, $roleId, 0);
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

      }
      catch (Exception $e)
      {
        $this->logger->error("Error while Validating an UL Registration",["ulRegistration"=>$ulEntity, Logger::$EXCEPTION=>$e]);
        $this->uniteLocaleDBService->transactionRollback();

        $this->response->getBody()->write(json_encode(["success"=>false,'message'=>"Une erreur est survenue lors du traitement de votre demande"]));
        return $this->response;
      }

      try
      {
        $this->createULInLowerEnv($ulEntity);
        $this->response->getBody()->write(json_encode(["success"=>true]));
      }
      catch(Exception $e)
      {
        $this->logger->error("Error while creating UL in lower env", [Logger::$EXCEPTION=>$e, "ul"=>$ulEntity]);
        $this->response->getBody()->write(json_encode(["success"=>true, "message"=>"Votre compte a été créer en production, mais une erreur est survenue lors de la création de votre compte sur le site de TEST. Veuillez contacter le support sur Slack."]));
      }

      return $this->response;
    }
    else
    {
      $this->response->getBody()->write(json_encode(["success"=>false,'message'=>"Le token fourni est incorrect. Soit ce n'est pas la bonne valeur, soit votre inscription a déjà été approuvée ou rejettée"]));
      return $this->response;
    }
  }


  private function createULInLowerEnv(UniteLocaleEntity $ulEntity)
  {
    $deploymentType = $this->emailBusinessService->getDeploymentEnvCode();
    if($deploymentType === "P")
    {
      $lowerEnv = "https://test.redcrossquest.croix-rouge.fr";
    }
    else if($deploymentType === "T")
    {
      $lowerEnv = "https://dev.redcrossquest.croix-rouge.fr";
    }
    else if($deploymentType === "D")
    {
      $lowerEnv = "http://localhost:3000";
    }

    $client        = new Client(
      [
        // Base URI is used with relative requests
        'base_uri' => $lowerEnv,
        // You can set any number of default request options.
        'timeout'  => 60,
      ]
    );

    $options = [ 'base_uri'  => $lowerEnv,
     // 'handler' => $stack,
      
      'headers'   => [
        'Content-Type'  => 'application/json; charset=utf-8',
        'Accept'        => 'application/json'
      ],
      'json' => ["ul"=>$ulEntity]
    ];

    $this->logger->debug("create_ul_in_lower_env - rest call options", ["options"=>$options]);

    $response = $client->request(
      'POST',
      "rest/ul_registration/create_ul_in_lower_env",
      $options);

    $decodedResponse = json_decode($response->getBody()->getContents(), true );

    $this->logger->debug("create_ul_in_lower_env - response", ["decodedResponse"=>$decodedResponse]);
  }
}
