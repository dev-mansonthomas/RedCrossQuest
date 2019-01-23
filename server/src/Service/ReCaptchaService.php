<?php
/**
 * Created by IntelliJ IDEA.
 * User: thomas
 * Date: 2019-01-14
 * Time: 21:14
 */

namespace RedCrossQuest\Service;



class ReCaptchaService
{

  /** @var \Monolog\Logger */
  protected $logger;

  protected $secretKey;
  protected $redCrossQuestHost;
  protected $lowestAcceptableScore;

  public function __construct($settings, $logger)
  {
    $this->logger                 = $logger;

    $this->secretKey              = $settings['ReCaptcha'  ]['secret'];
    $this->lowestAcceptableScore  = $settings['ReCaptcha'  ]['lowestAcceptableScore'];
    $appUrl                       = $settings['appSettings']['appUrl'];

    if($appUrl == "http://localhost:3000/")
    {
      $this->redCrossQuestHost = "localhost";
    }
    else
    {
      $this->redCrossQuestHost      = substr(substr(explode(":", $appUrl)[1],2), 0, -1);
    }


    $this->logger->addInfo("ReCaptcha Host Check is '".$this->redCrossQuestHost."'");
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
   * @param $actionRequired   string    what action is excpected by the server
   * @param $username         string    the username of the client when applicable
   *
   * @return                  int       0: Success, 1: token empty/null/too long, 2: wrong action, 3: score too low, 4: response is an error, 5: exception occurred while performing the check.
   *
   */
  public function verify($token, $actionRequired, $username)
  {
    //Google App Engine do not fill the REMOTE_ADDR, instead :
    /*
      [HTTP_X_FORWARDED_FOR] => <client_ip>, <google_proxy>
    */
    if(empty($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
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
        strlen($token) >  500  )
    {
      $this->logger->addError(
        "ReCaptcha Token is too long",
        array(
          'actionRequired'=> $actionRequired,
          'remoteIp'      => $remoteIP,
          'username'      => $username,
          'token length'  => strlen($token)
        )
      );
      return 1;
    }

    try
    {
      $resp = (new \ReCaptcha\ReCaptcha($this->secretKey))->setExpectedHostname($this->redCrossQuestHost)->verify($token, $remoteIP);

      if ($resp->isSuccess())
      {
        $action = $resp->getAction();
        $score  = $resp->getScore ();

        if($action != $actionRequired)
        {
          $this->logger->addError(
            "wrong action for reCaptcha",
            array(
              'actionRequired'=> $actionRequired,
              'clientAction'  => $action,
              'remoteIp'      => $remoteIP,
              'username'      => $username,
              'token'         => $token
            )
          );
          return 2;
        }

        if($score < $this->lowestAcceptableScore)
        {
          $this->logger->addError(
            "reCaptcha score is too low",
            array(
              'actionRequired'=> $actionRequired,
              'score'         => $score,
              'remoteIp'      => $remoteIP,
              'username'      => $username,
              'token'         => $token
            )
          );

          return 3;
        }

        $this->logger->addInfo("ReCaptcha test is a success, action is correct and score is above minimal value, proceeding to login",
          array('remoteIp'=>$remoteIP, 'username' => $username, 'score'=> $score, 'clientAction'=> $action, 'token' => $token));

        return 0;
      }
      else
      {
        $this->logger->addError(
          "reCaptcha general failure",
          array(
            'actionRequired'=> $actionRequired,
            'remoteIp'      => $remoteIP,
            'username'      => $username,
            'response'      => print_r($resp, true),
            'token'         => $token
          )
        );

        return 4;
      }
    }
    catch(\Exception $e)
    {
      $this->logger->addError(
        "Exception occurred during ReCaptcha Validation",
        array(
          'actionRequired'=> $actionRequired,
          'remoteIp'      => $remoteIP,
          'username'      => $username,
          'token'         => $token,
          'exception'     => print_r($e,true)
        )
      );
      return 5;
    }
  }
}