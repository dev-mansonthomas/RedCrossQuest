<?php
/**
 * Created by IntelliJ IDEA.
 * User: thomas
 * Date: 2019-01-17
 * Time: 08:02
 */

namespace RedCrossQuest\Service;

use Exception;
use SendGrid;
use SendGrid\Mail\Mail;

class MailService
{
  /** @var Logger */
  protected $logger;

  protected $sendgridAPIKey;
  protected $sendgridSender;
  protected $deploymentType;

  /**
   * @param Logger $logger          Logger instance
   * @param string $sendgridAPIKey  The SendGrid API
   * @param string $sendgridSender  The sender email address
   * @param string $deploymentType  The deploymentType
   */
  public function __construct(Logger $logger, $sendgridAPIKey, $sendgridSender, $deploymentType)
  {
    $this->logger         = $logger;
    $this->sendgridAPIKey = $sendgridAPIKey;
    $this->sendgridSender = $sendgridSender;
    $this->deploymentType = $deploymentType;
  }


  /**
   *
   * send an email without attachment
   *
   * @param string $application name of the Application (RedCrossQuest or RedQuest?)
   * @param string $mailType type of mail, for logging purpose
   * @param string $subject Subject for the email
   * @param string $recipientEmail Recipient email address
   * @param string $recipientFirstName Recipient First Name
   * @param string $recipientLastName Recipient Last Name
   * @param string $content Email html content
   * @param string|null $bcc The BCC email. A single email, or a ";" separated value list of email
   * @param string|null $fileName The filename that will be attached to the email. The file will be read from sys_get_temp_dir() and removed after the mail is sent
   * @param string|null $replyTo  Specify a reply to address, useful for the thankYou Mail where the reply is not intended to support
   * @return int Mail status code
   * @throws SendGrid\Mail\TypeException
   */
  public function sendMail(string $application,
                           string $mailType,
                           string $subject,
                           string $recipientEmail,
                           string $recipientFirstName,
                           string $recipientLastName,
                           string $content,
                           ?string $bcc=null,
                           ?string $fileName=null,
                           ?string $replyTo=null):int
  {
    $deployment        = self::getDeploymentInfo();
    $deploymentLogging = $deployment == '' ? "*PROD*": $deployment;
    $emailAddressesAdded = [];

    if($bcc == null || trim($bcc)=="")
    {
      $bcc = "support@redcrossquest.com";
    }
    else
    {
      $bcc = $bcc.";support@redcrossquest.com";
    }

    if($this->deploymentType==='D')
    {
      //do not send email to real addresses in Dev.
      //UL Registration requires to use the real emails
      $recipientEmail = "mt@mansonthomas.com";
    }
    
    try
    {
      $email = new Mail();
      $email->setFrom   ($this->sendgridSender,"$application");
      if($replyTo != null)
      {
        $email->setReplyTo($replyTo);
      }
      $email->setSubject("[$application]".$deployment.$subject);
      $email->addTo     ($recipientEmail, $recipientFirstName.' '.$recipientLastName);
      $emailAddressesAdded[]=$recipientEmail;
      //add BCC if required. Sendgrid raise an error if in {to, cc, bcc} there's duplicates

      if(strpos($bcc, ";")>0)
      {
        $mailList = explode(";", $bcc);
        foreach($mailList as $mailBCC)
        {
          if(!in_array($mailBCC, $emailAddressesAdded))
          {
            $email->addBcc    ($mailBCC);
            $emailAddressesAdded[]=$mailBCC;
          }
        }
      }
      else
      {
        if(!in_array($bcc, $emailAddressesAdded))
        {
          $email->addBcc($bcc);
          $emailAddressesAdded[] = $bcc;
        }
      }

      // Convert every non-ASCII character of the HTML body to a numeric HTML
      // entity (`é` -> `&#233;`). The resulting payload is 7-bit ASCII, so it
      // survives intermediate mail relays that mangle 8-bit UTF-8 bytes (seen
      // on some Croix-Rouge MTAs where accents arrive as U+FFFD). The HTML
      // renderer of the recipient mail client decodes the entities back to
      // the original characters at display time.
      $htmlBody = ($deployment!=''?$deployment.'<br/>':'').$content;
      $htmlBody = mb_encode_numericentity($htmlBody, [0x80, 0x10FFFF, 0, 0x10FFFF], 'UTF-8');
      $email->addContent("text/html", $htmlBody);

      if($fileName != null)
      {
        $email->addAttachment(new SendGrid\Mail\Attachment(base64_encode(file_get_contents(sys_get_temp_dir()."/".$fileName)),"application/zip", "$fileName"));
      }

      // Add unique headers to prevent Gmail thread grouping
      $randomHex = bin2hex(random_bytes(16));
      $email->addHeader('Message-ID', sprintf('<%s@redcrossquest.com>', $randomHex));
      $email->addHeader('X-Entity-Ref-ID', $randomHex);
      // Prevent In-Reply-To / References headers that cause threading
      // SendGrid does not add them unless set explicitly, so nothing to remove

      // Disable SendGrid click & open tracking. The branded link
      // url8294.redcrossquest.com is served over plain HTTP (enabling HTTPS
      // would require fronting it with a CDN + custom SSL certificate + a
      // SendGrid Support ticket - see docs.sendgrid.com/ui/analytics-and-reporting/click-tracking-ssl).
      // We track link engagement directly in RCQ (Spotfire uuid in the URL),
      // so SendGrid tracking adds no value and only degrades the user
      // experience with an HTTP warning on click.
      $email->setClickTracking(false, false);
      $email->setOpenTracking(false);

      $response = (new SendGrid($this->sendgridAPIKey))->send($email);

      if($fileName != null)
      {
        unlink(sys_get_temp_dir(). "/".$fileName);
      }

      $this->logger->info("Sendgrid return status code of '".$response->statusCode()."'",
        array(
          'mailType'       => $mailType,
          'deployment'     => $deploymentLogging,
          'recipientEmail' => $recipientEmail,
          'response'       => json_encode($response)));

      return $response->statusCode();

    }
    catch(Exception $e)
    {
      $this->logger->error("Error while sending email",
        array(
          'application'    => $application,
          'mailType'       => $mailType,
          'deployment'     => $deploymentLogging,
          'recipientEmail' => $recipientEmail,
          'subject'        => $subject,
          'content'        => $content,
          Logger::$EXCEPTION => $e));

      throw $e;
    }
  }

  /**
   * Return a string to be put in the email subjects
   * @return string nothing for production, [Site de DEV] for D, [Site de TEST] for T
   */
  private function getDeploymentInfo():string
  {
    $deployment='';
    if($this->deploymentType == 'D')
    {
      $deployment='[Site de DEV]';
    }
    else if($this->deploymentType == 'T')
    {
      $deployment='[Site de TEST]';
    }
    return $deployment;
  }

}
