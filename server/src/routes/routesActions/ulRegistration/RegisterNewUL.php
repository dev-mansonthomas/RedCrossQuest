<?php

namespace RedCrossQuest\routes\routesActions\ulRegistration;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use RedCrossQuest\BusinessService\EmailBusinessService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\DBService\UserDBService;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\routes\routesActions\Action;
use RedCrossQuest\Service\ClientInputValidator;
use RedCrossQuest\Service\ClientInputValidatorSpecs;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\ReCaptchaService;
use RedCrossQuest\Service\RedCallService;

class RegisterNewUL extends Action
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
                              RedCallService                $redCallService)
  {
    parent::__construct($logger, $clientInputValidator);
    $this->uniteLocaleDBService = $uniteLocaleDBService;
    $this->reCaptchaService     = $reCaptchaService;
    $this->userDBService        = $userDBService;
    $this->emailBusinessService = $emailBusinessService;
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

    $reCaptchaResponseCode = $this->reCaptchaService->verify($token, "rcq/registerNewUL", "RegisterNewUL");

    if($reCaptchaResponseCode > 0)
    {// error
      $this->logger->error("registerNewUL: ReCaptcha error ", array('token' => $token, 'ReCode'=>$reCaptchaResponseCode));

      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"An error occurred - ReCode $reCaptchaResponseCode"]));

      return $response401;
    }

    $ulEntity = new UniteLocaleEntity($this->parsedBody, $this->logger);

    try
    {
      $this->userDBService->checkExistingUserWithNivol($ulEntity->admin_nivol);
    }
    catch(Exception $e)
    {
      $this->logger->error("RegisterUL fails because Admin Nivol is already an active user",
        ["ulRegistration"=>$ulEntity, Logger::$EXCEPTION=>$e]);
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"Le nivol entré pour l'administrateur a déjà un compte actif dans RedCrossQuest"]));
      return $response401;
    }

   $checkCode = $this->checkPresident($ulEntity);

    if($checkCode>0)
    {
      $response401 = $this->response->withStatus(401);
      $response401->getBody()->write(json_encode(["error" =>"$checkCode"]));
      return $response401;
    }

    $ulEntity->registration_token = Uuid::uuid4()->toString();

    $registrationId = $this->uniteLocaleDBService->registerNewUL($ulEntity);
    $this->emailBusinessService->newULRegistrationEmailForPresidentWithToken($ulEntity);
    $this->emailBusinessService->newULRegistrationEmail($ulEntity);
    
    $this->response->getBody()->write(json_encode(["registrationId"=>$registrationId]));

    return $this->response;
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

    //IDs from RedCall are now in text since february 2022 (since CSV sync between pegass & redcall)
    if($ULInDB->external_id != $structureId)
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
