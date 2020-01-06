<?php
namespace RedCrossQuest\routes\routesActions\authentication;



class SendPasswordInitializationMailResponse
{
  /**
   * @var bool $success the action did succeed or not
   */
  public $success;

  /**
   * @var string  $email The email of the resetted password
   */
  public $email;

  protected $_fieldList = ['success', 'email'];

  public function __construct(bool $success, string $email=null)
  {
    $this->success  = $success;
    $this->email    = $email;
  }
}
