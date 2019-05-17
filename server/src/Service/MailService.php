<?php
/**
 * Created by IntelliJ IDEA.
 * User: thomas
 * Date: 2019-01-17
 * Time: 08:02
 */

namespace RedCrossQuest\Service;

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
   * @return int Mail status code
   * @throws \Exception                   when sending the email fails
   */
  public function sendMail($application,
                           $mailType,
                           $subject,
                           $recipientEmail,
                           $recipientFirstName,
                           $recipientLastName,
                           $content)
  {
    $deployment        = self::getDeploymentInfo();
    $deploymentLogging = $deployment == '' ? "*PROD*": $deployment;

    try
    {
      $email = new Mail();
      $email->setFrom   ($this->sendgridSender,"$application");
      $email->setSubject("[$application]".$deployment.$subject);
      $email->addTo     ($recipientEmail, $recipientFirstName.' '.$recipientLastName);
      $email->addContent("text/html", ($deployment!=''?$deployment.'<br/>':'').$content);

      $response = (new SendGrid($this->sendgridAPIKey))->send($email);

      $this->logger->info("Sending email successfully",
        array(
          'mailType'       => $mailType,
          'deployment'     => $deploymentLogging,
          'recipientEmail' => $recipientEmail,
          'response'       => print_r($response, true)));

      return $response->statusCode();

    }
    catch(\Exception $e)
    {
      $this->logger->error("Error while sending email",
        array(
          'application'    => $application,
          'mailType'       => $mailType,
          'deployment'     => $deploymentLogging,
          'recipientEmail' => $recipientEmail,
          'subject'        => $subject,
          'content'        => $content,
          'exception'      => print_r($e, true)));

      throw $e;
    }
  }

  /**
   * Return a string to be put in the email subjects
   * @return string nothing for production, [Site de DEV] for D, [Site de TEST] for T
   */
  private function getDeploymentInfo()
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