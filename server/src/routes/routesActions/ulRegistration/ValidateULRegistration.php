<?php

namespace RedCrossQuest\routes\routesActions\ulRegistration;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
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
use RedCrossQuest\Service\RedCallService;

class ValidateULRegistration extends Action
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
   * @var UserDBService        $userDBService
   */
  private $userDBService;

  /**
   * @var EmailBusinessService          $emailBusinessService
   */
  private $emailBusinessService;

  /**
   * @var RedCallService          $redCallService
   */
  private RedCallService                $redCallService;

  /**
   * @var QueteurDBService              $queteurDBService
   */
  private $queteurDBService;



  /**
   * @param LoggerInterface      $logger
   * @param ClientInputValidator $clientInputValidator
   * @param ReCaptchaService     $reCaptchaService
   * @param UserDBService        $userDBService
   * @param EmailBusinessService $emailBusinessService
   * @param UniteLocaleDBService $uniteLocaleDBService
   * @param RedCallService       $redCallService
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
    { // it means that the token is the one expected, so that the president did forward the token to the administror
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
        $queteurId     = $this->queteurDBService->insert        ($queteurEntity, $ulEntity->id, $roleId);
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
      $lowerEnv = "https://test.redcrossquest.croix-rouge.fr";
    }
    else if($deploymentType === "D")
    {
      $lowerEnv = "http://localhost:3000";
    }
/*
    $stack = HandlerStack::create();
    $stack->push(
      Middleware::log(
        $this->logger,
        new MessageFormatter('{req_body} - {res_body}')
      )
    );*/


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
      "rest/ul_registration/create_ul_in_lower_env",['debug' => true]);

    $decodedResponse = json_decode($response->getBody()->getContents(), true );

    $this->logger->debug("create_ul_in_lower_env - response", ["decodedResponse"=>$decodedResponse]);
  }

  private function checkPresident(UniteLocaleEntity $ulEntity):int
  {
    $nivolPresident  = str_pad( $ulEntity->president_nivol, 12, "0", STR_PAD_LEFT);
    $uri             = "admin/pegass?page=1&type=volunteer&identifier=$nivolPresident";

    $redCallResponse = $this->redCallService->get($uri);

    $this->logger->debug("RedCallResponse", ['URI'=>$uri,'RedCallResponse'=>$redCallResponse, "ULEntityHTTP_POST"   => $ulEntity]);

    $structureId  = $redCallResponse['payload']['entries']['0']['content']['user'   ]['structure']['id'];
    $skills       = $redCallResponse['payload']['entries']['0']['content']['skills' ];
    $user         = $redCallResponse['payload']['entries']['0']['content']['user'   ];
    $contacts     = $redCallResponse['payload']['entries']['0']['content']['contact'];

    /* check if :
     * NIVOL is the same as the request
     * has the same UL ID
     * has the same email
     * has the skill with ID 34 - Elu Local (Gouvernance Locale)
     */

    if($user["id"] !== $nivolPresident)
    {
      $this->logger->error("Error while checking President - Wrong NIVOL returned",
        [ "ULEntityHTTP_POST"   => $ulEntity,
          "PresidentFromRedCall"=> $redCallResponse,
          "RedCallURI"          => $uri
        ]);
      return RegisterNewUL::$CHECK_NIVOL_ERROR;
    }

    $emailFound = false;
    foreach($contacts as $oneContact)
    {
      if(strtoupper($oneContact['libelle']) === strtoupper($ulEntity->president_email))
      {
        $emailFound = true;
        break;
      }
    }

    if(!$emailFound)
    {
      $this->logger->error("Error while checking President - Email provided not found",
        [ "ULEntityHTTP_POST"   => $ulEntity,
          "PresidentFromRedCall"=> $redCallResponse,
          "RedCallURI"          => $uri
        ]);
      return RegisterNewUL::$CHECK_EMAIL_ERROR;
    }

    $ULInDB = $this->uniteLocaleDBService->getUniteLocaleById($ulEntity->id);

    if($ULInDB->external_id !== $structureId)
    {
      $this->logger->error("Error while checking President - Wrong Unité Locale",
        [ "ULEntityHTTP_POST"   => $ulEntity,
          "ULEntityInDB"        => $ULInDB,
          "PresidentFromRedCall"=> $redCallResponse,
          "RedCallURI"          => $uri
        ]);
      return RegisterNewUL::$CHECK_UL_ERROR;
    }

    $skillFound=false;

    foreach($skills as $skill)
    {
      if($skill['id'] == RegisterNewUL::$CORRECT_ELU_LOCAL_SKILL_ID)
      {
        $skillFound=true;
        break;
      }
    }

    if(!$skillFound)
    {
      $this->logger->error("Error while checking President - Correct skill Not Found",
        [ "ULEntityHTTP_POST"   => $ulEntity,
          "PresidentFromRedCall"=> $redCallResponse,
          "RedCallURI"          => $uri
        ]);
      return RegisterNewUL::$CHECK_SKILL_ERROR;
    }

    return 0;

  }

  public static int $CHECK_NIVOL_ERROR=1;
  public static int $CHECK_EMAIL_ERROR=2;
  public static int $CHECK_UL_ERROR=3;
  public static int $CHECK_SKILL_ERROR=4;

  private static int $CORRECT_ELU_LOCAL_SKILL_ID=34;
}
