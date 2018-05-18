<?php

namespace RedCrossQuest\BusinessService;


use RedCrossQuest\Entity\QueteurEntity;

class EmailBusinessService
{
  protected $logger;
  protected $mailer;
  protected $appSettings;

  public function __construct($logger, $mailer, $appSettings)
  {
    $this->logger       = $logger;
    $this->mailer       = $mailer;
    $this->appSettings  = $appSettings;
  }


  /**
   * Send an email to allow the user to reset its password (or create the password for the first connexion)
   * @param QueteurEntity $queteur  The information of the user
   * @param string        $uuid     The uuid to be inserted in the email
   * @throws \Exception   if the email fails to be sent
   */
  public function sendInitEmail(QueteurEntity $queteur, string $uuid)
  {


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




    $this->mailer->setFrom    ('thomas.manson@croix-rouge.fr', 'Thomas Manson');
    $this->mailer->addAddress ( $queteur->email, $queteur->first_name.' '.$queteur->last_name);
    $this->mailer->addBCC     ('thomas.manson@croix-rouge.fr');
    $this->mailer->Subject = "[RedCrossQuest]".$deployment." Réinitialisation de votre mot de passe";
    $this->mailer->Body = ($deployment!=''?$deployment.'<br/>':'')."
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
 RedCrossQuest<br/>
";

    if (!$this->mailer->send())
    {
      throw new \Exception("Error while sending email to ".$queteur->email." for ". $queteur->nivol." ".$this->mailer->ErrorInfo);
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


    $url=$this->appSettings['appUrl'];


    $this->mailer->setFrom    ('thomas.manson@croix-rouge.fr', 'Thomas Manson');
    $this->mailer->addAddress ( $queteur->email, $queteur->first_name.' '.$queteur->last_name);
    $this->mailer->addBCC     ('thomas.manson@croix-rouge.fr');
    $this->mailer->Subject = '[RedCrossQuest] Votre mot de passe a été changé';
    $this->mailer->Body = "
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
";

    if (!$this->mailer->send())
    {
      throw new \Exception("Error while sending email to ".$queteur->email." for ". $queteur->nivol." ".$this->mailer->ErrorInfo);
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




    $this->mailer->setFrom    ('thomas.manson@croix-rouge.fr', 'Thomas Manson');
    $this->mailer->addAddress ( $queteur->email, $queteur->first_name.' '.$queteur->last_name);
    $this->mailer->addBCC     ('thomas.manson@croix-rouge.fr');
    $this->mailer->Subject = "[RedCrossQuest]".$deployment." Suite à votre demande, vos données viennent d'être anonymisées";
    $this->mailer->Body = ($deployment!=''?$deployment.'<br/>':'')."
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
";

    if (!$this->mailer->send())
    {
      throw new \Exception("Error while sending anonymisation email to ".$queteur->email." for ". $queteur->nivol." ".$this->mailer->ErrorInfo);
    }
  }


}
