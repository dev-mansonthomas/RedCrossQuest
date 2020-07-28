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
   * @param string $application           name of the Application (RedCrossQuest or RedQuest?)
   * @param string $mailType              type of mail, for logging purpose
   * @param string $subject               Subject for the email
   * @param string $recipientEmail        Recipient email address
   * @param string $recipientFirstName    Recipient First Name
   * @param string $recipientLastName     Recipient Last Name
   * @param string $content               Email html content
   * @param string $fileName              The filename that will be attached to the email. The file will be read from sys_get_temp_dir() and removed after the mail is sent
   * @param string $bcc                   The BCC email. A single email, or a ";" separated value list of email
   * @return int Mail status code
   * @throws Exception                   when sending the email fails
   */
  public function sendMail($application,
                           $mailType,
                           $subject,
                           $recipientEmail,
                           $recipientFirstName,
                           $recipientLastName,
                           $content,
                           $bcc=null,
                           $fileName=null):int
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
    
    try
    {
      $email = new Mail();
      $email->setFrom   ($this->sendgridSender,"$application");
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

      $email->addContent("text/html", ($deployment!=''?$deployment.'<br/>':'').$content);

      if($fileName != null)
      {
        $email->addAttachment(new SendGrid\Mail\Attachment(base64_encode(file_get_contents(sys_get_temp_dir()."/".$fileName)),"application/zip", "$fileName"));
      }


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
          'exception'      => $e));

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
