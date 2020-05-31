<?php
namespace RedCrossQuest\Entity;



/**
 */
class PageableRequestEntity
{
  /**
   * @var int $pageNumber The number of the page to be displayed
   */
  public $pageNumber          ;
  /**
   * @var int $rowsPerPage Number of rows per pages
   */
  public $rowsPerPage            ;

  /**
   * @var array $filterMap key=>value (string=>string) filters
   */
  public $filterMap;

  protected $_fieldList = ['pageNumber', 'rowPerPage', 'filterMap'];

  /**
   * @param array $filterMap
   */
  public function __construct(array $filterMap)
  {
    //max value, the fact it's an int, is controlled in the Action class
    if(array_key_exists('pageNumber', $filterMap) && $filterMap['pageNumber'] !== null)
    {
      $this->pageNumber = $filterMap['pageNumber'];
    }
    else
    {
      $this->pageNumber = 0;
    }

    if(array_key_exists('rowsPerPage', $filterMap) && $filterMap['rowsPerPage'] !== null)
    {
      $this->rowsPerPage = $filterMap['rowsPerPage'];
    }
    else
    {
      $this->rowsPerPage = 30;
    }
    //so that there's not duplication / different values
    unset($filterMap['pageNumber']);
    unset($filterMap['rowPerPage']);

    $this->filterMap  = $filterMap ;
  }
}
