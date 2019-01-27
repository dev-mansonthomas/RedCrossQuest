<?php

namespace RedCrossQuest\BusinessService;


use Monolog\Logger;
use Ramsey\Uuid\Uuid;
use RedCrossQuest\DBService\MailingDBService;
use RedCrossQuest\Entity\MailingInfoEntity;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Entity\UniteLocaleEntity;

use RedCrossQuest\Service\MailService;

use Carbon\Carbon;


class EmailBusinessService
{
  /**
   * @var Logger
   */
  protected $logger;

  protected $appSettings;
  /**
   * @var MailingDBService
   * */
  protected $mailingDBService;

  /**
   * @var MailService
   * */
  protected $mailService;


  public function __construct($logger, $mailService, $mailingDBService, $appSettings)
  {

    $this->logger           = $logger;
    $this->appSettings      = $appSettings;
    $this->mailService      = $mailService;
    $this->mailingDBService = $mailingDBService;
  }


  /**
   * Send an email to allow the user to reset its password (or create the password for the first connexion)
   * @param QueteurEntity $queteur  The information of the user
   * @param string        $uuid     The uuid to be inserted in the email
   * @throws \Exception   if the email fails to be sent
   */
  public function sendInitEmail(QueteurEntity $queteur, string $uuid)
  {
    $this->logger->addInfo("sendInitEmail:'".$queteur->email."'");

    $url        = $this->appSettings['appUrl'].$this->appSettings['resetPwdPath'].$uuid;

    $startValidityDateCarbon = new Carbon();
    $startValidityDateString = $startValidityDateCarbon->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');

    $this->mailService->sendMail(
      "RedCrossQuest",
      "sendInitEmail",
      "[".$queteur->nivol."] Réinitialisation de votre mot de passe",
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      "
Bonjour ".$queteur->first_name.",<br/>
<br/>
 Cet email fait suite à votre demande de réinitialisation de mot de passe pour l'application RedCrossQuest.<br/>
 Votre login est votre NIVOL : <b>'".$queteur->nivol."'</b><br/>
 Si vous n'êtes pas à l'origine de cette demande, ignorer cet email.<br/>
<br/> 
 Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :<br/>
<br/> 
 <a href='".$url."' target='_blank'>".$url."</a><br/>
 Ce lien est valide une heure à compter du : ".$startValidityDateString."
<br/> 
<br/> 
 Cordialement,<br/>
 Le support de RedCrossQuest<br/>
");

  }


  /**
   *
   * Send a confirmation email to the user after password changed successfully
   *
   * @param QueteurEntity $queteur information about the user
   *
   * @throws \Exception if the mail fails to be sent
   *
   */
  public function sendResetPasswordEmailConfirmation(QueteurEntity $queteur)
  {
    $this->logger->addInfo("sendResetPasswordEmailConfirmation:'".$queteur->email."'");

    $url=$this->appSettings['appUrl'];

    $changePasswordDate = new Carbon();
    $changePasswordDateString = $changePasswordDate->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');

    $this->mailService->sendMail(
      "RedCrossQuest",
      "sendResetPasswordEmailConfirmation",
      "[".$queteur->nivol."] Votre mot de passe a été changé",
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      "
Bonjour ".$queteur->first_name.",<br/>
<br/>
 Cet email confirme le changement de votre mot de passe pour l'application RedCrossQuest le $changePasswordDateString.<br/>
 Votre login est votre NIVOL : '".$queteur->nivol."'
 Si vous n'êtes pas à l'origine de cette demande, contactez votre cadre local ou départementale.<br/>
<br/> 
 Vous pouvez maintenant vous connecter à RedCrossQuest avec votre nouveau mot de passe :<br/>
<br/> 
 <a href='".$url."' target='_blank'>".$url."</a><br/>
<br/> 
<br/> 
 Cordialement,<br/>
 RedCrossQuest<br/>
");

  }


  /**
   * Send an email that inform the queteur its data has been anonymised
   * @param QueteurEntity $queteur  The information of the user
   * @param string        $token     The uuid to be inserted in the email
   * @throws \Exception   if the email fails to be sent
   */
  public function sendAnonymizationEmail(QueteurEntity $queteur, string $token)
  {
    $this->logger->addInfo("sendAnonymizationEmail:'".$queteur->email."'");

    $anonymiseDateCarbon = new Carbon();
    $anonymiseDateString = $anonymiseDateCarbon->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');


    $this->mailService->sendMail(
      "RedCrossQuest",
      "sendAnonymizationEmail",
      $queteur->first_name.", Suite à votre demande, vos données viennent d'être anonymisées",
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      "
<p>Bonjour ".$queteur->first_name.",</p>

<p>
 Cet email fait suite à votre demande d'anonymisation de vos données personnelles de l'application RedCrossQuest, 
 l'outil de gestion opérationel de la quête de la Croix Rouge française.
</p>

<p>Tout d'abord, la Croix Rouge française tient à vous remercier pour votre contribution à la quête de la Croix Rouge.<br/>
Vous avez participé au financement des activités de premiers secours et d'actions sociales de l'unité locale de '".$queteur->ul_name."'<br/>
Nous espèrons vous revoir bientôt à la quête ou en tant que bénévole!
</p>

<p>Conformément à votre demande, vos données personnelles ont été remplacées par les valeurs indiquées ci-après :
  <ul>
   <li>Nom: 'Quêteur' </li>
   <li>Prénom: 'Anonimisé'</li>
   <li>Email: ''</li>
   <li>Secteur: 0</li>
   <li>NIVOL: ''</li>
   <li>Mobile: ''</li>
   <li>Date de Naissance: 22/12/1922</li>
   <li>Homme: 0</li> 
   <li>Active: 0</li>
  </ul>
 </p>

<p> 
La date d'anonymisation est le ".$anonymiseDateString." et ce token sont conservé dans notre base de données :
</p>
</p>TOKEN : '$token'</p>
 
<p>
  Si vous revenez preter main forte à l'unité locale de '".$queteur->ul_name."', vous pouvez communiquer ce Token à l'unité locale de '".$queteur->ul_name."'
  Il permettra de retrouver votre fiche anonymisée et de revaloriser votre fiche avec vos données pour une nouvelle participation à la quête!
  Vous retrouver ainsi vos statistiques des années passées.
  (ce token n'est valable que pour l'unité locale de '".$queteur->ul_name."', un nouveau compte sera créé si vous quêter avec une autre unité locale)
</p>
<p>
 Si vous n'êtes pas à l'origine de cette demande, contactez l'unité locale de '".$queteur->ul_name."' et donner leur ce token ainsi que les informations listées plus haut dans cet email pour revaloriser votre fiche.
</p>
 
<br/> 
 Cordialement,<br/>
 RedCrossQuest<br/>
");

  }



  /**
   * Send a batch of X emails to thanks Queteur for their participation
   * @param int $ul_id id of the UL
   * @param UniteLocaleEntity $uniteLocaleEntity s
   * @return MailingInfoEntity[] Mailing information with status
   * @throws \Exception when things goes wrong
   */
  public function sendThanksEmailBatch(int $ul_id, UniteLocaleEntity $uniteLocaleEntity)
  {
    $mailInfoEntity = $this->mailingDBService->getMailingInfo($ul_id, $this->appSettings['email']['thanksMailBatchSize']);

    if($mailInfoEntity != null)
    {
      $count = count($mailInfoEntity);
      for($i=0;$i<$count; $i++)
      {
        $mailInfoEntity[$i] = $this->sendThanksEmail($mailInfoEntity[$i], $uniteLocaleEntity);
      }
    }

    return $mailInfoEntity;
  }
  
  /**
   * Send an email to allow the user to reset its password (or create the password for the first connexion)
   * @param MailingInfoEntity $mailingInfoEntity  Info for the mailing
   * @param UniteLocaleEntity $uniteLocaleEntity  Info about the UL
   * @return MailingInfoEntity updated with token and status
   * @throws \Exception if mailing has an issue
   */
  public function sendThanksEmail(MailingInfoEntity $mailingInfoEntity, UniteLocaleEntity $uniteLocaleEntity)
  {
    //if spotfire_access_token not generated, generate it and store it
    if($mailingInfoEntity->spotfire_access_token == null || strlen($mailingInfoEntity->spotfire_access_token) != 36)
    {
      $mailingInfoEntity->spotfire_access_token = Uuid::uuid4()->toString();
      $this->mailingDBService->updateQueteurWithSpotfireAccessToken($mailingInfoEntity->spotfire_access_token, $mailingInfoEntity->id, $uniteLocaleEntity->id);
    }

    $rcqBanner = "<div style='background-color: #222222;'><img src=\"https://www.redcrossquest.com/assets/images/RedCrossQuestLogo.png\" style=\"height: 50px;\"/></div>";

    $emailContact = urlencode("
$rcqBanner

Bonjour la Croix Rouge de ".$uniteLocaleEntity->name.",<br/>
<br/>
J'ai une demande en relation avec <b>mes données personnelles et l'application RedCrossQuest</b>:<br/>
Note: cet email est à transférer au responsable de la quête, au trésorier ou au président de l'UL<br/>
<br/> 
------------------<br/>
<i>Votre demande ici</i><br/>
------------------<br/>
<br/>
<a href='https://www.redcrossquest.com/#!/queteurs/edit/".$mailingInfoEntity->id."' target='_blank'>Lien vers RCQ</a><br/>
<br/>
En vous remerciant,<br/>
".$mailingInfoEntity->first_name." ".$mailingInfoEntity->last_name.", ".$mailingInfoEntity->email.".<br/><br/>");


    $url        = $this->appSettings['appUrl'].$this->appSettings['graphPath']."?i=".$mailingInfoEntity->spotfire_access_token."&g=".$this->appSettings['queteurDashboard'];

    $startValidityDateCarbon = new Carbon();
    $startValidityDateString = $startValidityDateCarbon->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');

    try
    {

      $statusCode = $this->mailService->sendMail(
        "RedCrossQuest",
        "sendAnonymizationEmail",
        $mailingInfoEntity->first_name.", Merci pour votre Participation aux Journées Nationales de la Croix Rouge",
        $mailingInfoEntity->email,
        $mailingInfoEntity->first_name,
        $mailingInfoEntity->last_name,
        "
$rcqBanner
<br/>    
Bonjour ".$mailingInfoEntity->first_name.",<br/>
<br/>
Encore une fois nous tenions à te remercier pour ta participation aux journées nationales 2018 de la Croix-Rouge française !<br/>
<br/>
Nous t'avons préparé un petit résumé de ce que ta participation représente pour l'unité locale de ".$uniteLocaleEntity->name.". <br/>
Tu y trouveras également un message de remerciement de son Président. <br/>
<br/>
Pour cela, il suffit de cliquer sur l'image ci-dessous:<br/>
<a href='$url' target='_blank'>
<img src='https://www.redcrossquest.com/assets/images/RedCrossQuest-Merci.jpg' alt='Cliquez ICI'>
</a><br/>
<small style='color:silver;'>ou recopie l'addresse suivante dans ton navigateur:<br/>
<a href='$url' style='color:grey;'>$url</a>
</small>
<br/>
<br/>
Amicalement,<br/>
L'Unité Locale de ".$uniteLocaleEntity->name.",<br/>
".$uniteLocaleEntity->phone."<br/>
".$uniteLocaleEntity->email."<br/>
".$uniteLocaleEntity->address.", ".$uniteLocaleEntity->postal_code.", ".$uniteLocaleEntity->city."<br/>
<br/>
<small style='color:silver;'>
Cet email est envoyé depuis la plateforme RedCrossQuest qui permet aux unités locales de gérer les journées nationales.<br/>
Vos données ne sont utilisées que pour la gestion des Journées Nationales et ne sont pas partagées avec un tiers.<br/>
Notre politique de protection des données conforme à la RGPD est <a href=\"".$this->appSettings['RGPD']."\" target='_blank' style='color:grey;'>disponible ici</a>.<br/>
Vous pouvez demander à ne plus recevoir d'email de la platforme ou à corriger / anonymiser vos données par email<br/>
<a href=\"mailto:".$uniteLocaleEntity->email."?subject=[RedCrossQuest]Newsletter ou données personnelles&body=$emailContact\" style='color:grey;'>Contactez nous ici</a><br/>
<br/>
email envoyé le $startValidityDateString<br/>
</small>
");


      $mailingInfoEntity->status = $statusCode;
      $this->mailingDBService->insertQueteurMailingStatus($mailingInfoEntity->id, $mailingInfoEntity->status);
    }
    catch(\Exception $e)
    {
      $mailingInfoEntity->status = substr($e->getMessage()."", 0,200);
      $this->mailingDBService->insertQueteurMailingStatus($mailingInfoEntity->id, $mailingInfoEntity->status);

      //Do not rethrow, continue
    }

    return $mailingInfoEntity;
  }
}
