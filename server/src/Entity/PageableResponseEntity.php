<?php
namespace RedCrossQuest\Entity;

/**
 * @OA\Schema(schema="PageableResponseEntity", required={"rows","count","pageNumber","rowsPerPage"})
 */
class PageableResponseEntity
{
  /**
   * @OA\Property()
   * @var int $pageNumber The number of the page to be displayed
   */
  public $pageNumber          ;
  /**
   * @OA\Property()
   * @var int $rowsPerPage Number of rows per pages
   */
  public $rowsPerPage            ;

  /**
   * @OA\Property()
   * @var array $rows number=>object
   */
  public $rows;

  /**
   * @OA\Property()
   * @var int $count The number of rows matching the query
   */
  public $count          ;

  /**
   * to store additional info, like total, counts
   * @OA\Property()
   * @var array $additionalInfo string=>object
   */
  public $additionalInfo;

  protected array $_fieldList = ['pageNumber', 'rowPerPage', 'rows', 'count', 'additionalInfo'];

  /**
   * @param int $count
   * @param array $rows
   * @param int $pageNumber
   * @param $rowsPerPage
   */
  public function __construct(int $count, array $rows, int $pageNumber, $rowsPerPage )
  {
    $this->count        = $count;
    $this->rows         = $rows;
    $this->pageNumber   = $pageNumber;
    $this->rowsPerPage  = $rowsPerPage;
  }
}
