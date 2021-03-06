<?php
namespace RedCrossQuest\routes\routesActions\authentication;


/**
 * @OA\Schema(schema="ResetPasswordResponse", required={"success"})
 */
class ResetPasswordResponse
{
  /**
   * @OA\Property()
   * @var bool $success the action did succeed or not
   */
  public $success;

  /**
   * @OA\Property()
   * @var string  $email The email of the reset password
   */
  public $email;

  protected $_fieldList = ['success', 'email'];

  public function __construct(bool $success, string $email=null)
  {
    $this->success  = $success;
    $this->email    = $email;
  }
}
