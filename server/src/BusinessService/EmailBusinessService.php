<?php

namespace RedCrossQuest\BusinessService;


use RedCrossQuest\Entity\QueteurEntity;
use SendGrid;


class EmailBusinessService
{
  protected $logger;
  protected $sendgrid;
  protected $sendgridSender;
  protected $appSettings;

  public function __construct($logger, $sendgrid, $sendgridSender, $appSettings)
  {
    $this->logger         = $logger;
    $this->sendgrid       = $sendgrid;
    $this->sendgridSender = $sendgridSender;
    $this->appSettings    = $appSettings;
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

    $url=$this->appSettings['appUrl'].$this->appSettings['resetPwdPath'].$uuid;

    $deploymentType = $this->appSettings['deploymentType'];
    $deployment='';
    if($deploymentType == 'D')
    {
      $deployment='[Site de DEV]';
    }
    else if($deploymentType == 'T')
    {
      $deployment='[Site de TEST]';
    }


    $recipient = new SendGrid\Email($queteur->first_name.' '.$queteur->last_name, $queteur->email);
    $subject = "[RedCrossQuest]".$deployment."[".$queteur->nivol."] Réinitialisation de votre mot de passe";

    $body = new SendGrid\Content("text/html",  ($deployment!=''?$deployment.'<br/>':'')."
Bonjour ".$queteur->first_name.",<br/>
<br/>
 Cet email fait suite à votre demande de réinitialisation de mot de passe pour l'application RedCrossQuest.<br/>
 Votre login est votre NIVOL : <b>'".$queteur->nivol."'</b><br/>
 Si vous n'êtes pas à l'origine de cette demande, ignorer cet email.<br/>
<br/> 
 Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :<br/>
<br/> 
 <a href='".$url."' target='_blank'>".$url."</a><br/>
 Ce lien est valide une heure à compter de : ".date('d/m/Y H:i:s', time())."
<br/> 
<br/> 
 Cordialement,<br/>
 Le support de RedCrossQuest<br/>
");


    $mail = new SendGrid\Mail($this->sendgridSender, $subject, $recipient, $body);

    $response = $this->sendgrid->client->mail()->send()->post($mail);

    if($response->statusCode() < 200 || $response->statusCode() >= 300)
    {
      $this->logger->addError("sendInitEmail:'".$queteur->email." ERROR ", array('response'=>print_r($response, true)));

      throw new \Exception("sendInitEmail:Error while sending email to '".$queteur->email."''  ".print_r($response, true));
    }
    else
    {
      $this->logger->addInfo("sendInitEmail:'".$queteur->email."' SUCCESS");
    }
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

    $deploymentType = $this->appSettings['deploymentType'];
    $deployment='';
    if($deploymentType == 'D')
    {
      $deployment='[Site de DEV]';
    }
    else if($deploymentType == 'T')
    {
      $deployment='[Site de TEST]';
    }

    $recipient = new SendGrid\Email($queteur->first_name.' '.$queteur->last_name, $queteur->email);
    $subject = "[RedCrossQuest]".$deployment."[".$queteur->nivol."] Votre mot de passe a été changé";

    $body = new SendGrid\Content("text/html", ($deployment!=''?$deployment.'<br/>':'')."
Bonjour ".$queteur->first_name.",<br/>
<br/>
 Cet email confirme le changement de votre mot de passe pour l'application RedCrossQuest.<br/>
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

    $mail = new SendGrid\Mail($this->sendgridSender, $subject, $recipient, $body);

    $response = $this->sendgrid->client->mail()->send()->post($mail);

    if($response->statusCode() < 200 || $response->statusCode() >= 300)
    {
      $this->logger->addError("sendResetPasswordEmailConfirmation:'".$queteur->email." ERROR ", array('response'=>print_r($response, true)));
      throw new \Exception("sendResetPasswordEmailConfirmation:Error while sending email to ".$queteur->email." ".print_r($response, true));
    }
    else
    {
      $this->logger->addInfo("sendResetPasswordEmailConfirmation:'".$queteur->email."' SUCCESS");
    }
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

    $deploymentType = $this->appSettings['deploymentType'];
    $deployment='';
    if($deploymentType == 'D')
    {
      $deployment='[Site de DEV]';
    }
    else if($deploymentType == 'T')
    {
      $deployment='[Site de TEST]';
    }

    $recipient = new SendGrid\Email($queteur->first_name.' '.$queteur->last_name, $queteur->email);
    $subject = "[RedCrossQuest]".$deployment." ".$queteur->first_name.", Suite à votre demande, vos données viennent d'être anonymisées";


    $body = new SendGrid\Content("text/html",  ($deployment!=''?$deployment.'<br/>':'')."
<p>Bonjour ".$queteur->first_name.",</p>

<p>
 Cet email fait suite à votre demande d'anonymisation de vos données personnelles de l'application RedCrossQuest, 
 l'outil de gestion opérationel de la quête de la Croix Rouge française.
</p>

<p>Tout d'abord, la Croix Rouge française tient à vous remercier pour votre contribution à la quête de la Croix Rouge.<br/>
Votre contribution a participé au financement des activités de premiers secours et d'actions sociales de l'unité locale de '".$queteur->ul_name."'<br/>
Nous espèrons vous revoir bientôt à la quête ou en tant que bénévole!
</p>

<p>Conformément à votre demande, vos données personnelles ont été remplacées par les valeurs indiquées ci-après:
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
La date d'anonymisation ".date('d/m/Y H:i:s', time())." et ce token sont conservé dans notre base de données :
</p>
</p>TOKEN : '$token'</p>
 
<p>
  Si vous revenez preter main forte à l'unité locale de '".$queteur->ul_name."', vous pouvez communiquer ce Token. 
  Il permettra de retrouver votre fiche anonymisée et de revaloriser votre fiche.
  Vous retrouver ainsi vos statistiques des années passées.
</p>
<p>
 Si vous n'êtes pas à l'origine de cette demande, contactez l'unité locale de '".$queteur->ul_name."' et donner leur ce token ainsi que les informations listées plus haut dans cet email pour recréer votre fiche.
</p>
 
<br/> 
 Cordialement,<br/>
 RedCrossQuest<br/>
");

    $mail = new SendGrid\Mail($this->sendgridSender, $subject, $recipient, $body);

    $response = $this->sendgrid->client->mail()->send()->post($mail);

    if($response->statusCode() < 200 || $response->statusCode() >= 300)
    {
      $this->logger->addError("sendInitEmail:'".$queteur->email." ERROR ", array('response'=>print_r($response, true)));
      throw new \Exception("sendAnonymizationEmail:Error while sending email to ".$queteur->email." ".print_r($response, true));
    }
    else
    {
      $this->logger->addInfo("sendAnonymizationEmail:'".$queteur->email."' SUCCESS");
    }
  }
}
