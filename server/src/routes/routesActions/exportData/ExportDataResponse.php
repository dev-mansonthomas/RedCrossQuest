<?php
namespace RedCrossQuest\routes\routesActions\exportData;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ExportDataResponse", required={"status", "email", "fileName", "numberOfRows",})
 */
class ExportDataResponse
{
  /**
   * @OA\Property()
   * @var ?int $status mail api status code
   */
  public ?int $status;

  /**
   * @OA\Property()
   * @var ?string  $email The email where the export has been sent
   */
  public ?string $email;

  /**
   * @OA\Property()
   * @var ?string  $fileName the name of the export file
   */
  public ?string $fileName;

  /**
   * @OA\Property()
   * @var ?int $numberOfRows The email where the export has been sent
   */
  public ?int $numberOfRows;

  protected array $_fieldList = ["status", "email", "fileName", "numberOfRows"];

  public function __construct(int $status, string $email, string $fileName, int $numberOfRows)
  {
    $this->status       = $status;
    $this->email        = $email;
    $this->fileName     = $fileName;
    $this->numberOfRows = $numberOfRows;
  }
}
