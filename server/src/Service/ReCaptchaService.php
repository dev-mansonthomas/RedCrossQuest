<?php
/**
 * Created by IntelliJ IDEA.
 * User: thomas
 * Date: 2019-01-14
 * Time: 21:14
 */

namespace RedCrossQuest\Service;
use Exception;
use ReCaptcha\ReCaptcha;


class ReCaptchaService
{

  /** @var Logger */
  protected $logger;

  protected $secretKey;
  protected $redCrossQuestHost;
  protected $lowestAcceptableScore;

  public function __construct($settings, Logger $logger, string $recaptchaSecret)
  {
    $this->logger                 = $logger;

    $this->secretKey              = $recaptchaSecret;
    $this->lowestAcceptableScore  = $settings['ReCaptcha'  ]['lowestAcceptableScore'];
    $appUrl                       = $settings['appSettings']['appUrl'];

    if($appUrl == "http://localhost:3000/")
    {
      $this->redCrossQuestHost = "localhost";
    }
    else if($appUrl == "http://rcq:3000/")
    {
      $this->redCrossQuestHost = "rcq";
    }
    else
    {
      $this->redCrossQuestHost      = substr(substr(explode(":", $appUrl)[1],2), 0, -1);
    }


    $this->logger->info("ReCaptcha Host Check is '".$this->redCrossQuestHost."'");
  }

//Changer les callbacks en un return d'un object


  /**
   *  Perform a ReCaptcha validation, with the hostname and remoteIp checks
   *  * check that the token is not null, empty or length > 500
   *  * in case of success
   *  * check the action send by the client match the one expected by the server ($actionRequired)
   *  * check that the score returned by ReCaptcha is greater than the one defined as the lowest acceptable value in the server configuration
   *
   * @param $token            string    the token sent by the client
   * @param $actionRequired   string    what action is expected by the server
   * @param $username         string    the username of the client when applicable
   * @param array|null $parsedBody array     Reference to the parsedBody for logging purpose if an error occurs
   *
   * @return                  int       0: Success, 1: token empty/null/too long, 2: wrong action, 3: score too low, 4: response is an error, 5: exception occurred while performing the check.
   */
  public function verify(string $token, string $actionRequired, string $username, ?array &$parsedBody=[]):int
  {
    //Google App Engine do not fill the REMOTE_ADDR, instead :
    /*
      [HTTP_X_FORWARDED_FOR] => <client_ip>, <google_proxy>
    */
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
    {
      $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
      $remoteIP = $ips[0] ;
    }
    else
    {
      $remoteIP = $_SERVER['REMOTE_ADDR'];
    }
    //discard token that are too long, null or empty
    if( $token         == null ||
        strlen($token) == 0    ||
        strlen($token) >  1500  )
    {
      $this->logger->error(
        "ReCaptcha Token is too long",
        array(
          'actionRequired'=> $actionRequired,
          'remoteIp'      => $remoteIP,
          'username'      => $username,
          'token length'  => strlen($token),
          'parsedBody'    => &$parsedBody
        )
      );
      return 1;
    }

    try
    {
      //if the URL entered by the client has www. then we check the domain against www.$this->redCrossQuestHost (ex: www.redcrossquest.croix-rouge.fr)
      $hasWWW = strtolower(substr( $_SERVER['HTTP_HOST'], 0, 4 )) === "www.";
      $resp = (new ReCaptcha($this->secretKey))->setExpectedHostname(($hasWWW?"www.":"").$this->redCrossQuestHost)->verify($token, $remoteIP);

      if ($resp->isSuccess())
      {
        $action = $resp->getAction();
        $score  = $resp->getScore ();

        if($action != $actionRequired)
        {
          $this->logger->error(
            "wrong action for reCaptcha",
            array(
              'actionRequired'=> $actionRequired,
              'clientAction'  => $action,
              'remoteIp'      => $remoteIP,
              'username'      => $username,
              'token'         => $token,
              'parsedBody'    => &$parsedBody
            )
          );
          return 2;
        }

        if($score < $this->lowestAcceptableScore)
        {
          $this->logger->error(
            "reCaptcha score is too low",
            array(
              'actionRequired'=> $actionRequired,
              'score'         => $score,
              'remoteIp'      => $remoteIP,
              'username'      => $username,
              'token'         => $token,
              'parsedBody'    => &$parsedBody
            )
          );

          return 3;
        }

        $this->logger->info("ReCaptcha test is a success, action is correct and score is above minimal value, proceeding to login",
          array('remoteIp'=>$remoteIP, 'username' => $username, 'score'=> $score, 'clientAction'=> $action, 'token' => $token));

        return 0;
      }
      else
      {
        $this->logger->error(
          "reCaptcha general failure",
          array(
            'actionRequired'=> $actionRequired,
            'remoteIp'      => $remoteIP,
            'username'      => $username,
            'response'      => json_encode($resp),
            'parsedBody'    => &$parsedBody,
            'token'         => $token
          )
        );

        return 4;
      }
    }
    catch(Exception $e)
    {
      $this->logger->error(
        "Exception occurred during ReCaptcha Validation",
        array(
          'actionRequired'=> $actionRequired,
          'remoteIp'      => $remoteIP,
          'username'      => $username,
          'token'         => $token,
          Logger::$EXCEPTION     => $e,
          'parsedBody'    => &$parsedBody
        )
      );
      return 5;
    }
  }
}
