<?php
namespace RedCrossQuest\routes\routesActions\queteurs;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ExportDataQueteurResponse", required={"status", "email", "fileName", "numberOfRows",})
 */
class ExportDataQueteurResponse
{
  /**
   * @OA\Property()
   * @var int|null $status mail api status code
   */
  public ?int $status;

  /**
   * @OA\Property()
   * @var string|null  $email The email where the export has been sent
   */
  public ?string $email;

  /**
   * @OA\Property()
   * @var string|null  $fileName the name of the export file
   */
  public ?string $fileName;

  /**
   * @OA\Property()
   * @var int|null $numberOfRows The email where the export has been sent
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
