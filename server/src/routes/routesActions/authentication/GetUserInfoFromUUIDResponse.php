<?php
namespace RedCrossQuest\routes\routesActions\authentication;



class GetUserInfoFromUUIDResponse
{
  /**
   * @var bool $success the action did succeed or not
   */
  public $success;

  /**
   * @var string  $nivol The nivol corresponding to the UUID
   */
  public $nivol;

  protected $_fieldList = ['success', 'nivol'];

  public function __construct(bool $success, string $nivol=null)
  {
    $this->success  = $success;
    $this->nivol    = $nivol;
  }
}
