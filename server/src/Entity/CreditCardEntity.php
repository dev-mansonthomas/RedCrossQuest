<?php
namespace RedCrossQuest\Entity;

use Carbon\Carbon;
use Exception;
use OpenApi\Annotations as OA;
use Psr\Log\LoggerInterface;

/**
 * @OA\Schema(schema="CreditCardEntity", required={"tronc_queteur_id", "ul_id", "quantity", "amount"})
 */
class CreditCardEntity  extends Entity
{
  /**
   * @OA\Property()
   * @var ?int $id Id of the CreditCard Entry
   */
  public ?int $id               ;

  /**
   * @OA\Property()
   * @var ?int $tronc_queteur_id ID of the TroncQueteur the CreditCard Entry is attached to
   */
  public ?int $tronc_queteur_id  ;

  /**
   * @OA\Property()
   * @var ?int $ul_id UL ID of the CreditCard Entry
   */
  public ?int $ul_id        ;

  /**
   * @OA\Property()
   * @var ?int $quantity Number of credit card ticket of that amount
   */
  public ?int $quantity       ;

  /**
   * @OA\Property()
   * @var ?float $amount amount of money per credit card ticket
   */
  public ?float $amount       ;

  /**
   * @OA\Property()
   * @var ?bool $delete should this line be deleted
   */
  public ?bool $delete = false ;



  protected array $_fieldList = ["id", "tronc_queteur_id", "ul_id", "quantity", "amount", "delete"];
  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array &$data, LoggerInterface $logger)
  {
    parent::__construct($logger);

    $this->getInteger('id'                , $data, 0);
    $this->getInteger('tronc_queteur_id'  , $data, 0);
    $this->getInteger('ul_id'             , $data, 0);
    $this->getInteger('quantity'          , $data);
    $this->getFloat  ('amount'            , $data);
    $this->getBoolean('delete'            , $data, false);
  }
}
