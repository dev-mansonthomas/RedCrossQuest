<?php
namespace RedCrossQuest\Middleware;

class DecodedToken
{
  /**
   * True if authenticated
   * @var bool $authenticated
   */
  private $authenticated ;
  /**
   * Error code set ini AuthorisationMiddleware
   * @var string $errorCode
   */
  private $errorCode     ;
  /**
   * Username (NIVOL)
   * @var string $username
   */
  private $username      ;
  /**
   * Id of the user
   * @var int $uid
   */
  private $uid           ;
  /**
   * ID of the UL of The User
   * @var int $ulId
   */
  private $ulId          ;
  /**
   * Name of the UL of the User
   * @var string $ulName
   */
  private $ulName        ;
  /**
   * Mode of use of RCQ.
   * 1 : RCQ is used to record dailyStats (dailystats before RCQ)
   * 2 : Normal mode
   * 3 : Deprecated, was aimed for Country Side UL, functionnality moved to RedQuest
   * @var int $ulMode
   */
  private $ulMode        ;
  /**
   * id of the queteur of the connected user
   * @var int $queteurId
   */
  private $queteurId     ;
  /**
   * Role of the connected user :  1:viewer,2:operator,3:counter,4:admin,9:superadmin
   * @var int $roleId
   */
  private $roleId        ;
  /**
   * Environment code:
   * 'D':dev, 'T':test, 'P':prod
   * @var string $d
   */
  private $d             ;


  /**
   * Init teh DecodedToken as an authentication failure (this->authenticated=false) with an error code
   * @param string $errorCode  the error code associated with the failed authentication request
   */
  public function __construct($errorCode)
  {
    $this->authenticated = false;
    $this->errorCode     = $errorCode;
  }

  /**
   * init the DecodedToken with data decoded from the token... ;)
   * @param boolean $authenticated true if the token is considered as valid, false otherwise
   * @param string $errorCode the string representing the error code '0004' for ex.
   * @param string $username the nivol of the user
   * @param string $uid the id of the user
   * @param string $ulId the id of the unite local to which the user belong to
   * @param string $ulName the name of the unite locale to which the user belong to
   * @param string $ulMode How the UniteLocal manage the quete (like Paris, like Marseille, like small city)
   * @param string $queteurId the id in the queteur table of the user
   * @param string $roleId the role id of the user (what can he do in the application)
   * @param string $deploymentType How the application is currently deployed: test/uat/production
   * @return DecodedToken an instance
   */
  public static function withData(bool    $authenticated, string $errorCode,
                                  string  $username     , string $uid      , string $ulId     ,
                                  string  $ulName       , string $ulMode   , string $queteurId,
                                  string  $roleId       , string $deploymentType):DecodedToken
  {
    $instance = new self($errorCode);

    $instance->setAuthenticated ($authenticated    );
    $instance->setUsername      ($username         );
    $instance->setUid           (intval($uid)      );
    $instance->setUlId          (intval($ulId)     );
    $instance->setUlName        ($ulName           );
    $instance->setUlMode        (intval($ulMode   ));
    $instance->setQueteurId     (intVal($queteurId));
    $instance->setRoleId        (intval($roleId)   );
    $instance->setD             ($deploymentType   );


    return $instance;
  }

  /**
   * @return string the json representation of this object
   */
  public function __toString():string
  {
    return json_encode($this->toArray());
  }

  /**
   * @return array the array version  of this object
   */
  public function toArray():array
  {
    return [  "authenticated" => $this->authenticated ,
      "errorCode"     => $this->errorCode     ,
      "username"      => $this->username      ,
      "uid"           => $this->uid           ,
      "ulId"          => $this->ulId          ,
      "ulName"        => $this->ulName        ,
      "ulMode"        => $this->ulMode        ,
      "queteurId"     => $this->queteurId     ,
      "roleId"        => $this->roleId        ,
      "d"             => $this->d
    ];
  }


  /**
   * @return string
   */
  public function getUlName():string
  {
    return $this->ulName;
  }

  /**
   * @param bool $authenticated
   */
  public function setAuthenticated($authenticated)
  {
    $this->authenticated = $authenticated;
  }

  /**
   * @param string $errorCode
   */
  public function setErrorCode($errorCode)
  {
    $this->errorCode = $errorCode;
  }

  /**
   * @param string $username
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }

  /**
   * @param int $uid
   */
  public function setUid($uid)
  {
    $this->uid = $uid;
  }

  /**
   * @param int $ulId
   */
  public function setUlId($ulId)
  {
    $this->ulId = $ulId;
  }

  /**
   * @param int $queteurId
   */
  public function setQueteurId($queteurId)
  {
    $this->queteurId = $queteurId;
  }

  /**
   * @param int $roleId
   */
  public function setRoleId($roleId)
  {
    $this->roleId = $roleId;
  }

  /**
   * @param string $ulName
   */
  public function setUlName($ulName)
  {
    $this->ulName = $ulName;
  }

  /**
   * @return string
   */
  public function getErrorCode():string
  {
    return $this->errorCode;
  }

  /**
   * @return int
   */
  public function getQueteurId(): int
  {
    return $this->queteurId;
  }

  /**
   * @return bool
   */
  public function getAuthenticated():bool
  {
    return $this->authenticated;
  }

  /**
   * @return int
   */
  public function getRoleId():int
  {
    return $this->roleId;
  }

  /**
   * @return int
   */
  public function getUid():int
  {
    return $this->uid;
  }

  /**
   * @return int
   */
  public function getUlId():int
  {
    return $this->ulId;
  }

  /**
   * @return string
   */
  public function getUsername():string
  {
    return $this->username;
  }

  /**
   * @return int
   */
  public function getUlMode():int
  {
    return $this->ulMode;
  }

  /**
   * @return string
   */
  public function getD():string
  {
    return $this->d;
  }

  /**
   * @param int $ulMode
   */
  public function setUlMode($ulMode)
  {
    $this->ulMode = $ulMode;
  }

  /**
   * @param string $d
   */
  public function setD($d)
  {
    $this->d = $d;
  }
}
