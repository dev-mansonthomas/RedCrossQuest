<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="SpotfireAccessEntity", required={"id","token","token_expiration","ul_id","user_id"})
 */

class SpotfireAccessEntity extends Entity
{
  /**
   * @OA\Property()
   * @var int $id the SpotfireAccess ID
   */
  public $id                          ;
  /**
   * @OA\Property()
   * @var string $token the token
   */
  public $token                       ;
  /**
   * @OA\Property()
   * @var Carbon $token_expiration the token expiration date
   */
  public $token_expiration            ;
  /**
   * @OA\Property()
   * @var int $ul_id the current user's UL ID
   */
  public $ul_id                       ;
  /**
   * @OA\Property()
   * @var int $user_id the current user's ID
   */
  public $user_id                     ;

  protected $_fieldList = ['id','token','token_expiration','ul_id','user_id'];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws \Exception if a parse Date or JSON fails
   */
  public function __construct(array $data, LoggerInterface $logger)
  {
    parent::__construct($logger);
    $this->getInteger('id'                        , $data);
    $this->getString ('token'                     , $data, 36);
    $this->getDate   ('token_expiration'          , $data);
    $this->getInteger('ul_id'                     , $data);
    $this->getInteger('user_id'                   , $data);

  }
}
