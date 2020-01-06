<?php
namespace RedCrossQuest\routes\routesActions\exportData;



class ExportDataResponse
{
  /**
   * @var int $status mail api status code
   */
  public $status;

  /**
   * @var string  $email The email where the export has been sent
   */
  public $email;

  /**
   * @var string  $fileName the name of the export file
   */
  public $fileName;

  /**
   * @var int $numberOfRows The email where the export has been sent
   */
  public $numberOfRows;

  protected $_fieldList = ["status", "email", "fileName", "numberOfRows"];

  public function __construct(int $status, string $email, string $fileName, int $numberOfRows)
  {
    $this->status       = $status;
    $this->email        = $email;
    $this->fileName     = $fileName;
    $this->numberOfRows = $numberOfRows;
  }
}
