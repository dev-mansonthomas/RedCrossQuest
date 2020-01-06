<?php
namespace RedCrossQuest\routes\routesActions\authentication;



class AuthenticationResponse
{
  /**
   * @var string token  JWT
   */
  public $token;

  protected $_fieldList = ['token'];


  public function __construct(string $token)
  {
    $this->token = $token;
  }
}
