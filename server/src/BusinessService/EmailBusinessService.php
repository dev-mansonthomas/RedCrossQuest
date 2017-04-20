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


  public function sendInitEmail(QueteurEntity $queteur, $uuid)
  {


    $url=$this->appSettings['appUrl'].$this->appSettings['resetPwdPath'].$uuid;


    $this->mailer->setFrom('thomas.manson@croix-rouge.fr', 'Thomas Manson');
    $this->mailer->addAddress( $queteur->email, $queteur->first_name.' '.$queteur->last_name);
    $this->mailer->Subject = 'Réinitialisation de votre mot de passe RedCrossQuest';
    $this->mailer->Body = "
Bonjour ".$queteur->first_name.",<br/>
<br/>
 Cet email fait suite à votre demande de réinitialisation de mot de passe pour l'application RedCrossQuest.<br/>
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

  public function sendResetPasswordEmailConfirmation(QueteurEntity $queteur)
  {


    $url=$this->appSettings['appUrl'];


    $this->mailer->setFrom('thomas.manson@croix-rouge.fr', 'Thomas Manson');
    $this->mailer->addAddress( $queteur->email, $queteur->first_name.' '.$queteur->last_name);
    $this->mailer->Subject = 'Votre mot de passe RedCrossQuest a été changé';
    $this->mailer->Body = "
Bonjour ".$queteur->first_name.",<br/>
<br/>
 Cet email confirme le changement de votre mot de passe pour l'application RedCrossQuest.<br/>
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
}
