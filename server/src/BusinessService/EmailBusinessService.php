<?php

namespace RedCrossQuest\BusinessService;


use Carbon\Carbon;
use Exception;
use Ramsey\Uuid\Uuid;
use RedCrossQuest\DBService\MailingDBService;
use RedCrossQuest\DBService\UniteLocaleDBService;
use RedCrossQuest\Entity\MailingInfoEntity;
use RedCrossQuest\Entity\QueteurEntity;
use RedCrossQuest\Entity\UniteLocaleEntity;
use RedCrossQuest\Service\Logger;
use RedCrossQuest\Service\MailService;


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
   * @var UniteLocaleDBService
   * */
  protected $uniteLocaleDBService;

  /**
   * @var MailService
   * */
  protected $mailService;


  public function __construct(Logger                $logger,
                              MailService           $mailService,
                              MailingDBService      $mailingDBService,
                              UniteLocaleDBService  $uniteLocaleDBService,
                              $appSettings)
  {

    $this->logger               = $logger;
    $this->appSettings          = $appSettings;
    $this->mailService          = $mailService;
    $this->mailingDBService     = $mailingDBService;
    $this->uniteLocaleDBService = $uniteLocaleDBService;
  }

  /**
   *
   * Send a confirmation email to admin, tresorier and president for the Registration to RedCrossQuest
   *
   * @param UniteLocaleEntity $ulRegistration UL registration info
   *
   * @throws Exception if the mail fails to be sent
   *
   */
  public function newULRegistrationEmail(UniteLocaleEntity $ulRegistration): void
  {
    $this->logger->info("newULRegistrationEmail:'", ['ulRegistration' =>$ulRegistration]);

    $url=$this->appSettings['appUrl'];

    $uniteLocaleEntity = $this->uniteLocaleDBService->getUniteLocaleById($ulRegistration->id);

    $registrationDate = Carbon::now();
    $registrationDateString = $registrationDate->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');

    $queteurData = ["id"=>0, "first_name"=>"Inscription", "last_name"=>"UL", "email"=>"n@a.com"];
    $queteur = new QueteurEntity($queteurData, $this->logger);

    $title="L'inscription de votre Unité Locale a été enregistrée";

    $this->mailService->sendMail(
      "RedCrossQuest",
      "newULRegistrationEmail",
       $title,
      $ulRegistration->admin_email,
      $ulRegistration->admin_first_name,
      $ulRegistration->admin_last_name,
      $this->getMailHeader($title, $ulRegistration->admin_first_name).
      "
<br/>
 Cet email confirme l'inscription de votre unité locale à l'application RedCrossQuest le $registrationDateString.<br/><br/>
 
 Merci de bien vouloir rejoindre <a href='https://intranet.croix-rouge.fr/jcms/p2_3891464/fr/redcrossquest' target='_blank'>l'espace intranet</a> dédié, ainsi que le <a href='https://join.slack.com/t/redcrossquest-forum/shared_invite/zt-703rcrd2-K2yT_j19vW8FBk34F~AQdw' target='_blank'> forum interactif Slack</a><br/>
 
 Pour rappel, RedCrossQuest/RedQuest, ce sont deux sites web dédiés à la gestion de la quête, un forum d'entraide (Slack) et un site intranet<br/>
 <ul>
    <li><a href='https://redcrossquest.croix-rouge.fr' target='_blank'>RedCrossQuest</a>
      <ul>
          <li>Le site de gestion de la quête par vos cadres et votre trésorier</li>
          <li>Gestion des bénévoles et bénévoles d'un jour</li>
          <li>Gestion de la préparation, du départ, du retour et du comptage des troncs</li>
          <li>Gestion des dons avec reçu fiscaux</li>
          <li>Gestion des données comptables de la quête : montant, remplissage des sacs de banque avec détails par pièces/billets/poids/total €</li>
          <li>Analyses des données avec de nombreux graphiques : Montant total quêté, comparaison avec les années précédentes, nombre de bénévoles, heures de quêtes, classements, meilleurs points de quête</li>
          <li>Mailing de remerciement aux quêteurs avec un texte personnalisable, l'affichage d'une carte avec les points de quête ou le quêteur a quêté et le montant quêté</li>
      </ul>
    </li>
    <li><a href='https://redquest.croix-rouge.fr' target='_blank'>RedQuest</a>
        <ul>
          <li>Le site compagnon du quêteur</li>
          <li>Qu'est ce que la Croix Rouge, ses principes, pourquoi on fait la quête, ou va l'argent et toutes les questions qu'on peut poser au quêteur</li>    
          <li>Les astuces pour bien quêter</li>
          <li>Le système de Badge (ou <a href='https://fr.wikipedia.org/wiki/Ludification' target='_blank'>gamification</a>) : récompenser virtuellement les quêteurs pour leurs contributions pour les inciter à se donner encore plus</li>
          <li>Un classement (configurable) des quêteurs de l'UL (pas de classement, 10 premiers, classement complet)</li>
          <li>Où est mon prochain point de quête, enregistrement autonome des départs/retours par le quêteur</li>
          <li>Historique de ses troncs de l'année et récapitulatif des années précédentes</li>
          <li>Information sur mon unité locale</li>
      </ul>
    </li>
    <li><a href='https://join.slack.com/t/redcrossquest-forum/shared_invite/zt-703rcrd2-K2yT_j19vW8FBk34F~AQdw' target='_blank'>Le forum interactif Slack</a>
        <ul>
          <li>Messagerie instantanée organisé par thème : posez votre question, la communauté ou le support vous répond</li>
          <li>Échanger vos astuces pour négocier avec les banques, motiver vos bénévoles</li>    
      </ul>
    </li>
    <li><a href='https://intranet.croix-rouge.fr/jcms/p2_3891464/fr/redcrossquest' target='_blank'>L'espace intranet</a>
        <ul>
          <li>Retrouver la documentation, les infos </li>
          <li>Les enregistrements des webinaires (formation, résutlats de la dernière quête)</li>    
      </ul>
    </li>

 </ul>
 
 
 Une présentation plus <a href='https://docs.google.com/presentation/d/1en_icDl5bjFyyNPomBUV1S_QksYmVzq_l4bnreE3Tnw/edit?usp=sharing' target='_blank'>complête est disponible ici</a> 
 
 
<br/>
 Rappel des éléments de votre inscription :
<ul>
    <li>Unité Locale : $uniteLocaleEntity->name </li>
    <li>Administrateur RedCrossQuest :
      <ul>
        <li>$ulRegistration->admin_first_name $ulRegistration->admin_last_name </li>
        <li>$ulRegistration->admin_nivol</li>
        <li>$ulRegistration->admin_email</li>                  
        <li>+$ulRegistration->admin_mobile</li>
        <li>".($ulRegistration->admin_man?"Homme":"Femme")."</li>
      </ul>
    </li>
    <li> Trésorier de l'Unité Locale :
      <ul>
        <li>$ulRegistration->tresorier_first_name $ulRegistration->tresorier_last_name </li>
        <li>$ulRegistration->tresorier_nivol</li>
        <li>$ulRegistration->tresorier_email</li>                  
        <li>+$ulRegistration->tresorier_mobile</li>
        <li>".($ulRegistration->tresorier_man?"Homme":"Femme")."</li>
      </ul>
    
    </li>
    <li> Président de l'Unité Locale :
      <ul>
        <li>$ulRegistration->president_first_name $ulRegistration->president_last_name </li>
        <li>$ulRegistration->president_nivol</li>
        <li>$ulRegistration->president_email</li>                  
        <li>+$ulRegistration->president_mobile</li>
        <li>".($ulRegistration->president_man?"Homme":"Femme")."</li>
      </ul>
    
    </li> 
</ul>

 Votre inscription va prochainement être validée et vous serez recontacté par email<br/>
 
 <a href='https://goo.gl/UpTLAK' target='_blank'>RedCrossQuest & la RGPD</a>
".$this->getMailFooter($uniteLocaleEntity, false, $queteur),
      $ulRegistration->president_email.";".$ulRegistration->tresorier_email);

  }


  /**
   * Send an email to allow the user to reset its password (or create the password for the first connexion)
   * @param QueteurEntity $queteur    The information of the user
   * @param string        $uuid       The uuid to be inserted in the email
   * @param bool          $firstInit  If it's the first init, the TTL of the link is 48h, otherwise 4h
   * @throws Exception   if the email fails to be sent
   */
  public function sendInitEmail(QueteurEntity $queteur, string $uuid, bool $firstInit = false): void
  {
    $url        = $this->appSettings['appUrl'].$this->appSettings['resetPwdPath'].$uuid;

    $startValidityDateCarbon = Carbon::now();
    $startValidityDateString = $startValidityDateCarbon->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');

    $uniteLocaleEntity = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id);

    if($firstInit)
      $mailTTL = "48 heures";
    else
      $mailTTL = "4  heures";

    $this->logger->debug("sending Mail to init password", ["mail"=>$queteur->email, 'url'=> $url, "mailTTL"=>$mailTTL]);
    
    $title = "Réinitialisation de votre mot de passe";

    $this->mailService->sendMail(
      "RedCrossQuest",
      "sendInitEmail",
      "[".$queteur->nivol."] $title",
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      $this->getMailHeader($title, $queteur->first_name).
      "
<br/>
 Cet email fait suite à votre demande de réinitialisation de mot de passe pour l'application RedCrossQuest.<br/>
 Votre login est votre NIVOL : <b>'".$queteur->nivol."'</b><br/>
 Si vous n'êtes pas à l'origine de cette demande, ignorer cet email.<br/>
<br/> 
 Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :<br/>
<br/> 
 <a href='".$url."' target='_blank'>".$url."</a><br/>
 Ce lien est valide $mailTTL à compter du : ".$startValidityDateString."
<br/> 
<br/>".$this->getMailFooter($uniteLocaleEntity, false, $queteur),
      $uniteLocaleEntity->admin_email);

  }

  /**
   *
   * Send a confirmation email to the user after password changed successfully
   *
   * @param QueteurEntity $queteur information about the user
   *
   * @throws Exception if the mail fails to be sent
   *
   */
  public function sendResetPasswordEmailConfirmation(QueteurEntity $queteur): void
  {
    $this->logger->info("sendResetPasswordEmailConfirmation:'", ['email' =>$queteur->email]);

    $url=$this->appSettings['appUrl'];

    $uniteLocaleEntity = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id);

    $changePasswordDate = Carbon::now();
    $changePasswordDateString = $changePasswordDate->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');

    $title="Votre mot de passe a été changé";

    $this->mailService->sendMail(
      "RedCrossQuest",
      "sendResetPasswordEmailConfirmation",
      "[".$queteur->nivol."] $title",
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      $this->getMailHeader($title, $queteur->first_name).
      "
<br/>
 Cet email confirme le changement de votre mot de passe pour l'application RedCrossQuest le $changePasswordDateString.<br/>
 Votre login est votre NIVOL : '".$queteur->nivol."'
 Si vous n'êtes pas à l'origine de cette demande, contactez votre cadre local ou départementale.<br/>
<br/> 
 Vous pouvez maintenant vous connecter à RedCrossQuest avec votre nouveau mot de passe :<br/>
<br/> 
 <a href='".$url."' target='_blank'>".$url."</a><br/>
<br/> 
".$this->getMailFooter($uniteLocaleEntity, false, $queteur),
      $uniteLocaleEntity->admin_email);

  }


  /**
   * Send an email to the queteur that made an export data request with his data
   * @param QueteurEntity $queteur        The information of the connected user
   * @param string        $zipFileName    The file name to attach to the email
   * @return string                       The status code from Sendgrid
   * @throws Exception   if the email fails to be sent
   */
  public function sendExportDataQueteur(QueteurEntity $queteur, string $zipFileName):string
  {
    $uniteLocaleEntity = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id);

    $title = "Export de vos données";

    return $this->mailService->sendMail(
      "RedCrossQuest",
      "exportDataQueteur",
      "[Confidentiel] $title",
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      $this->getMailHeader($title, $queteur->first_name).
      "
<br/>
 Cet email fait suite à votre demande d'export de vos données personnelles stockée dans l'application RedCrossQuest/RedQuest<br/>
<br/>
<strong>Attention :</strong> <br/>
<ul>
<li>Les données stockées dans RedCrossQuest & RedQuest sont le strict nécessaire à la bonne organisation de la quête de la Croix Rouge</li>
<li>Les données sont stockées sur les serveurs de la Croix Rouge française qui se situent en europe</li>
<li>Il n'y a pas de suivi temps réel de vos coordonnées GPS. On défini avant votre départ, l'endroit ou vous allez quêter qui lui à des coordonnées GPS pour l'affichage sur une carte</li>
<li>Si vous avez des questions, n'hésitez pas à contacter l'unité locale pour laquelle vous avez Quêté.</li>
<li>Il vous est possible de demander l'anonymisation de vos données à votre unité locale. Vous receverez alors une confirmation par email avec un Jeton. Avec ce jeton, vous pourrez l'année suivante, si vous revenez quêter dans la meme unité locale, revaloriser vos noms/prénoms et retrouver vos statistiques/badges dans RedQuest</li>
</ul>
<br/>".$this->getMailFooter($uniteLocaleEntity, false, $queteur),
      $uniteLocaleEntity->admin_email,
      $zipFileName);

  }


  /**
   * Send an email to the connected user with the data export of the UL
   * @param QueteurEntity $queteur        The information of the connected user
   * @param string        $zipFileName    The file name to attach to the email
   * @return string                       The status code from Sendgrid
   * @throws Exception   if the email fails to be sent
   */
  public function sendExportDataUL(QueteurEntity $queteur, string $zipFileName):string
  {
    $uniteLocaleEntity = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id);

    $title = "Export des données de votre Unité Locale";

    return $this->mailService->sendMail(
      "RedCrossQuest",
      "exportDataUL",
      "[Confidentiel] $title",
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      $this->getMailHeader($title, $queteur->first_name).
      "
<br/>
 Cet email fait suite à votre demande d'export des données de votre Unité Locale.<br/>
<br/>
<strong>Attention :</strong> <br/>
<ul>
<li>Cette archive contient <strong>les données personnelles</strong> de vos bénévoles et bénévoles d'un jour</li>
<li>Prenez toutes les précautions nécessaire pour que ces données ne soient pas diffusées en dehors du minimum de personnes ayant besoin d'avoir accès a ces informations.</li>
<li>Ces données ont été collectés pour les Journées Nationale, n'utilisez pas ces données hors du cadre des Journées Nationales !</li>
<li>Après utilisation des données, pensez a supprimer le fichier zip et la version décompressée. Si on vous vole votre ordi, ou vous le perdez, vous seriez responsable d'une fuite de données</li>
<li>Si vous devez conserver ces données, stockez les dans un espace de stockage sécurisé</li>
</ul>
<br/>".$this->getMailFooter($uniteLocaleEntity, false, $queteur),
      $uniteLocaleEntity->admin_email,
      $zipFileName);

  }



  /**
   * Send an email that inform the queteur its data has been anonymised
   * @param QueteurEntity $queteur  The information of the user
   * @param string        $token     The uuid to be inserted in the email
   * @throws Exception   if the email fails to be sent
   */
  public function sendAnonymizationEmail(QueteurEntity $queteur, string $token, bool $userAnonymised): void
  {
    $this->logger->info("sendAnonymizationEmail:'", ["email"=>$queteur->email]);

    $uniteLocaleEntity = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id);

    $anonymiseDateCarbon = Carbon::now();
    $anonymiseDateString = $anonymiseDateCarbon->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');

    $userAnonymisedHtml  = "";

    if($userAnonymised)
    {
      $userAnonymisedHtml = "
  <li><b>Utilisateur RCQ</b>
    <ul>
      <li>NIVOL: ''</li>
      <li>Password: 'N/A'</li>
      <li>Active: false</li>
      <li>Role: 0</li> 
    </ul>  
  </li>
";
    }


    $title = "Suite à votre demande, vos données viennent d'être anonymisées";

    $this->mailService->sendMail(
      "RedCrossQuest",
      "sendAnonymizationEmail",
      $title,
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      $this->getMailHeader($title, $queteur->first_name)."

<p>
 Cet email fait suite à votre demande d'anonymisation de vos données personnelles de l'application RedCrossQuest, 
 l'outil de gestion opérationnel de la quête de la Croix Rouge française.
</p>

<p>Tout d'abord, la Croix Rouge française tient à vous remercier pour votre contribution à la quête de la Croix Rouge.<br/>
Vous avez participé au financement des activités de premiers secours et d'actions sociales de l'unité locale de '".$queteur->ul_name."'<br/>
Nous espérons vous revoir bientôt à la quête ou en tant que bénévole!
</p>

<p>Conformément à votre demande, vos données personnelles ont été remplacées par les valeurs indiquées ci-après :
  <ul>
    <li>Prénom: 'Quêteur' </li>
    <li>Nom: 'Anonimisé'</li>
    <li>Email: ''</li>
    <li>Secteur: 0</li>
    <li>NIVOL: ''</li>
    <li>Mobile: ''</li>
    <li>Date de Naissance: 22/12/1922</li>
    <li>Homme: 0</li> 
    <li>Active: 0</li>
$userAnonymisedHtml
  </ul>
 </p>

<p> 
La date d'anonymisation est le ".$anonymiseDateString." et ce token sont conservé dans notre base de données :
</p>
</p>TOKEN : '$token'</p>
 
<p>
  Si vous revenez prêter main forte à l'unité locale de '".$queteur->ul_name."', vous pouvez communiquer ce Token à l'unité locale de '".$queteur->ul_name."'
  Il permettra de retrouver votre fiche anonymisée et de revaloriser votre fiche avec vos données pour une nouvelle participation à la quête!
  Vous retrouver ainsi vos statistiques des années passées.
  (ce token n'est valable que pour l'unité locale de '".$queteur->ul_name."', un nouveau compte sera créé si vous quêter avec une autre unité locale)
</p>
<p>
 Si vous n'êtes pas à l'origine de cette demande, contactez l'unité locale de '".$queteur->ul_name."' et donner leur ce token ainsi que les informations listées plus haut dans cet email pour revaloriser votre fiche.
</p>
".$this->getMailFooter($uniteLocaleEntity, false, $queteur));

  }



  /**
   * Send a batch of X emails to thanks Queteur for their participation
   * @param int $ul_id id of the UL
   * @param UniteLocaleEntity $uniteLocaleEntity s
   * @return MailingInfoEntity[] Mailing information with status
   * @throws Exception when things goes wrong
   */
  public function sendThanksEmailBatch(int $ul_id, UniteLocaleEntity $uniteLocaleEntity):array
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
   * @throws Exception if mailing has an issue
   */
  public function sendThanksEmail(MailingInfoEntity $mailingInfoEntity, UniteLocaleEntity $uniteLocaleEntity): MailingInfoEntity
  {
    //if spotfire_access_token not generated, generate it and store it
    if($mailingInfoEntity->spotfire_access_token == null || strlen($mailingInfoEntity->spotfire_access_token) != 36)
    {
      $mailingInfoEntity->spotfire_access_token = Uuid::uuid4()->toString();
      $this->mailingDBService->updateQueteurWithSpotfireAccessToken($mailingInfoEntity->spotfire_access_token, $mailingInfoEntity->id, $uniteLocaleEntity->id);
    }

    $url = $this->appSettings['appUrl'].$this->appSettings['graphPath']."?i=".$mailingInfoEntity->spotfire_access_token."&g=".$this->appSettings['queteurDashboard'];

    try
    {

      $title = $mailingInfoEntity->first_name.", Merci pour votre Participation aux Journées Nationales de la Croix Rouge";
      $statusCode = $this->mailService->sendMail(
        "RedCrossQuest",
        "sendAnonymizationEmail",
        $title,
        $mailingInfoEntity->email,
        $mailingInfoEntity->first_name,
        $mailingInfoEntity->last_name,
        $this->getMailHeader($title, $mailingInfoEntity->first_name)."
<br/>
Encore une fois nous tenions à te remercier pour ta participation aux journées nationales ".(Carbon::now())->year." de la Croix-Rouge française !<br/>
<br/>
Nous t'avons préparé un petit résumé de ce que ta participation représente pour l'unité locale de ".$uniteLocaleEntity->name.". <br/>
Tu y trouveras également un message de remerciement de son Président. <br/>
<br/>
Pour cela, il suffit de cliquer sur l'image ci-dessous:<br/>
<a href='$url' target='_blank'>
<img src='https://redcrossquest.croix-rouge.fr/assets/images/RedCrossQuest-Merci.jpg' alt='Cliquez ICI'>
</a><br/>
<small style='color:silver;'>ou recopie l'addresse suivante dans ton navigateur:<br/>
<a href='$url' style='color:grey;'>$url</a>
</small>
<br/>
<br/>
". $this->getMailFooter($uniteLocaleEntity, true, $mailingInfoEntity));


      $mailingInfoEntity->status = $statusCode;
      $this->mailingDBService->insertQueteurMailingStatus($mailingInfoEntity->id, $mailingInfoEntity->status);
    }
    catch(Exception $e)
    {
      $mailingInfoEntity->status = substr($e->getMessage()."", 0,200);
      $this->mailingDBService->insertQueteurMailingStatus($mailingInfoEntity->id, $mailingInfoEntity->status);

      //Do not rethrow, continue
    }

    return $mailingInfoEntity;
  }

  /**
   * @param string $title the title of the email
   * @param string $bonjour the text that will be displayed after the "Bonjour word
   * @param bool $RedQuest if true, mailing for RedQuest, then we use the RedQuest logo instead of RedCrossQuest
   * @return string return the html of the mail header
   */
  public function getMailHeader(string $title, string $bonjour, bool $RedQuest=false): string
  {
    return "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" lang='fr'>
<head>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>
  <title>[RedCrossQuest] $title</title>
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>
<body>
  <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"500\">
  <tr>
    <td style=\"background-color:#FFFFFF;\">
      <table style=\"width:100%;padding:0; margin:0;\" >
        <tr>
          <td style=\"font-family: Helvetica, Arial, sans-serif; font-size: 24px;font-weight: bolder;padding:8px;\">
            <div style='background-color:#FFFFFF;'><img src=\"https://".$this->getDeploymentInfo()."redcrossquest.croix-rouge.fr/assets/images/Red".($RedQuest?"":"Cross")."QuestLogo.png\" style=\"height:60px;\" height='60' alt='logo'/></div>
          </td>
          <td style=\"text-align: right;\"><img src=\"https://".$this->getDeploymentInfo()."redcrossquest.croix-rouge.fr/assets/images/logoCRF.png\" alt=\"Croix Rouge Française\" style=\"height: 90px;\" height='90'/></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style=\"background-color:#e3001b;padding-top:4px;padding-bottom:4px;text-align: center;vertical-align: top;\">
      &nbsp;
    </td>
  </tr>
  <tr>
    <td style=\"padding-top:20px;padding-bottom: 20px;text-align: center;\"><strong style=\"color:#054752;font-family: Arial, sans-serif;text-decoration:none;font-size:20px;line-height:18px;\"
    > $title </strong></td>
  </tr>
  <tr>
    <td style=\"padding:5px;font-family:Arial,sans-serif;color:#202020;font-size:16px;text-align:left;background-color: #ffffff;\">

      <strong>Bonjour $bonjour,</strong>
      <br/>

    
    ";
  }

  /**
   * @param UniteLocaleEntity $uniteLocaleEntity UL info
   * @param bool $isNewsletter : if true, the wording of the footer is slightly different
   * @param mixed $queteurInfo : QueteurEntity or MailingInfoEntity : an object with the info of the queteur.
   * @param bool $RedQuest if true, mailing for RedQuest, then we use the term RedQuest instead of RedCrossQuest
   * @return string return the html of the mail header
   */
  public function getMailFooter(UniteLocaleEntity $uniteLocaleEntity, bool $isNewsletter, $queteurInfo, bool $RedQuest=false): string
  {
    $startValidityDateCarbon = Carbon::now();
    $startValidityDateString = $startValidityDateCarbon->setTimezone("Europe/Paris")->format('d/m/Y à H:i:s');

    $text1 = $isNewsletter ? "ne plus recevoir d'email de la plateforme ou à" : "" ;
    $text2 = $isNewsletter ? "Newsletter ou données personnelles" : "Données personnelles" ;
    $text3 = $isNewsletter ? "la newsletter ou " : "" ;

    $emailContact = urlencode($this->getDeploymentType()."
Bonjour la Croix Rouge de ".$uniteLocaleEntity->name.",

J'ai une demande en relation avec $text3 mes données personnelles et l'application Red".($RedQuest?"":"Cross")."Quest:
Note: cet email est à transférer au responsable de la quête, au trésorier ou au président de l'UL

------------------
Votre demande ici
------------------

https://".$this->getDeploymentInfo()."redcrossquest.croix-rouge.fr/#!/queteurs/edit/$queteurInfo->id

En vous remerciant,
".$queteurInfo->first_name." ".$queteurInfo->last_name.", 
".$queteurInfo->email.".");



    return "
     <p>
        <span style=\"font-size: 15px;color:grey\">
        Amicalement,<br>
L'Unité Locale de ".$uniteLocaleEntity->name.",<br/>
".$uniteLocaleEntity->phone."<br/>
".$uniteLocaleEntity->email."<br/>
".$uniteLocaleEntity->address.", ".$uniteLocaleEntity->postal_code.", ".$uniteLocaleEntity->city."<br/>
Via l'application Red".($RedQuest?"":"Cross")."Quest.
        </span>
      </p>
    </td>
  </tr>
  <tr>
    <td style=\"background-color:azure; color:silver;text-align: justify;\">
Cet email est envoyé depuis la plateforme Red".($RedQuest?"":"Cross")."Quest qui permet aux unités locales de gérer les Journées Nationales.<br/>
Vos données ne sont utilisées que pour la gestion des Journées Nationales et ne sont pas partagées avec un tiers.<br/>
Notre politique de protection des données conforme à la RGPD est <a href=\"".$this->appSettings['RGPD']."\" target='_blank' style='color:grey;'>disponible ici</a>.<br/>
Vous pouvez demander à $text1 corriger / anonymiser vos données par email<br/>
<a href=\"mailto:".$uniteLocaleEntity->email."?subject=".$this->getDeploymentType()."[Red".($RedQuest?"":"Cross")."Quest]$text2&body=$emailContact\" style='color:grey;'>Contactez votre unité locale ici</a><br/>
<br/>
email envoyé le $startValidityDateString<br/>
    </td>
  </tr>
</table>

</body>
</head>
</html>
";
  }


  /**
   * Return the subdomain for links to RCQ depending on the current environment
   * @return string 'www.' for production, 'dev.' for D, 'test.' for T
   */
  private function getDeploymentInfo():string
  {
    $deployment='www.';
    if($this->appSettings['deploymentType'] == 'D')
    {
      $deployment='dev.';
    }
    else if($this->appSettings['deploymentType'] == 'T')
    {
      $deployment='test.';
    }
    return $deployment;
  }

  /**
   * Return a string to be put in the email subjects
   * @return string nothing for production, [Site de DEV] for D, [Site de TEST] for T
   */
  private function getDeploymentType():string
  {
    $deployment='';
    if($this->appSettings['deploymentType'] == 'D')
    {
      $deployment='[Site de DEV]';
    }
    else if($this->appSettings['deploymentType'] == 'T')
    {
      $deployment='[Site de TEST]';
    }
    return $deployment;
  }




  //RedQuest Mailing



  /**
   *
   * Send the Queteur an email to notify him of the approval decision
   *
   * @param QueteurEntity $queteur information about the user
   * @param bool $decision decision about the approval
   * @param string $rejectMessage reject message in case of refusal
   *
   * @throws Exception if the mail fails to be sent
   *
   */
  public function sendRedQuestApprovalDecision(QueteurEntity $queteur, bool $decision, string $rejectMessage=""):void
  {
    $this->logger->info("sendRedQuestApprovalDecision", ["email" => $queteur->email, "decision"=> $decision, "reject_reason"=> $rejectMessage]);

    $url="https://".$this->appSettings['RedQuestDomain']."/login";

    $uniteLocaleEntity = $this->uniteLocaleDBService->getUniteLocaleById($queteur->ul_id);

    $title="Votre inscription à RedQuest a été ".($decision?"approuvée":"refusée");

    if($decision)
    {
      $message = "
<br/>
 Votre inscription a été validée : Bienvenue sur RedQuest et encore merci pour votre participation à la quête de la Croix Rouge !<br/>
 <br/>
 Vous pouvez maintenant vous connecter à RedQuest et profiter de toutes ses fonctionnalités :<br/>
<br/> 
 <a href='".$url."' target='_blank'>".$url."</a><br/>
<br/> 

<span style='color:white; font-size: 1px;'>queteur id : ".$queteur->id.", registration_id:".$queteur->registration_id.", queteur_registration_token:".$queteur->queteur_registration_token.", ul_registration_token:".$queteur->ul_registration_token.", </span>
";
    }
    else
    {
      $message = "
<br/>
 Ohoh... Nous sommes désolé, votre inscription à RedQuest a été refusée avec le message suivant : 
 
 <hr>
 $rejectMessage
 <hr>

Si vous pensez qu'il y a une erreur, veuillez contacter votre Unité Locale.  
";
    }

    $this->mailService->sendMail(
      "RedQuest",
      "sendRedQuestApprovalDecision",
      $title,
      $queteur->email,
      $queteur->first_name,
      $queteur->last_name,
      $this->getMailHeader($title, $queteur->first_name, true).
      $message
      .$this->getMailFooter($uniteLocaleEntity, false, $queteur, true));
  }


  /**
   *
   * Send the Queteur an email to notify him of the approval decision
   *
   * @param UniteLocaleEntity $uniteLocaleEntity
   * @throws Exception if the mail fails to be sent
   */
  public function sendULRegistrationApprovalDecision(UniteLocaleEntity $uniteLocaleEntity):void
  {
    $this->logger->info("sendRedQuestApprovalDecision", ["email" => $uniteLocaleEntity->admin_email, "decision"=> $uniteLocaleEntity->registration_approved, "reject_reason"=> $uniteLocaleEntity->reject_reason]);

    $url="https://".$this->appSettings['RedQuestDomain']."/login";


    $title="Votre inscription à RedCrossQuest a été ".($uniteLocaleEntity->registration_approved?"approuvée":"refusée");

    if($uniteLocaleEntity->registration_approved)
    {
      $message = "
<br/>
Félicitations ! L'inscription de votre Unité Locale à RedCrossQuest/RedQuest a été validée!<br/>
 <br/>
L'administrateur a reçu un email lui permettant d'initialiser son mot de passe.
<ul>
    <li>N'attendez pas pour initialiser votre mot de passe, le lien est valable 48h. Passé ce délai, il suffit d'utiliser la fonction 'Réinitialiser mon mot de passe' sur la page de <a href='https://".$this->getDeploymentInfo()."redcrossquest.croix-rouge.fr' target='_blank'>login</a></li>
    <li>Seul le compte de l'administrateur a été créé, il revient à l'administrateur de créer les comptes du président et du trésorier</li>
    <li>Une fois connecté, vous aurez une liste d'instructions à suivre pour finir le paramétrage de l'application et vous préparer</li>
    <li>Le support est apporté via <a href='https://intranet.croix-rouge.fr/jcms/p2_3891464/fr/redcrossquest' target='_blank'>l'espace intranet</a> dédié, ainsi que le <a href='https://join.slack.com/t/redcrossquest-forum/shared_invite/zt-703rcrd2-K2yT_j19vW8FBk34F~AQdw' target='_blank'> forum interactif Slack</a></li>
    <li>Vous disposez aussi d'un site de Test <a href='https://test.redcrossquest.croix-rouge.fr' target='_blank'>https://test.redcrossquest.croix-rouge.fr</a> sur lequel vous pouvez vous entrainer.</li>    
</ul>

<span style='color:white; font-size: 1px;'>queteur id : ".$uniteLocaleEntity->registration_id.", ul_id:".$uniteLocaleEntity->id." </span>
";
    }
    else
    {
      $message = "
<br/>
 Ohoh... Nous sommes désolé, votre inscription à RedCrossQuest a été refusée par le support avec le message suivant : 
 
<hr>
$uniteLocaleEntity->reject_reason
<hr>

Si vous pensez qu'il y a une erreur, veuillez contacter le support : <a href='mailto:support@redcrossquest.com' target='_blank'>support@redcrossquest.com</a>.
<span style='color:white; font-size: 1px;'>queteur id : ".$uniteLocaleEntity->registration_id.", ul_id:".$uniteLocaleEntity->id." </span>  
";
    }

    $queteurData = ["id"=>0, "first_name"=>$uniteLocaleEntity->admin_first_name, "last_name"=> $uniteLocaleEntity->admin_last_name, "email"=>$uniteLocaleEntity->admin_email];
    $queteur = new QueteurEntity($queteurData, $this->logger);

    $this->mailService->sendMail(
      "RedCrossQuest",
      "sendULRegistrationApprovalDecision",
      $title,
      $uniteLocaleEntity->admin_email,
      $uniteLocaleEntity->admin_first_name,
      $uniteLocaleEntity->admin_last_name,
      $this->getMailHeader($title, $uniteLocaleEntity->admin_first_name, false).
      $message
      .$this->getMailFooter($uniteLocaleEntity, false, $queteur, false),
    "$uniteLocaleEntity->president_email;$uniteLocaleEntity->tresorier_email");
  }

}
