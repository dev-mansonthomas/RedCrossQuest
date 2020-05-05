<?php
namespace RedCrossQuest\DBService;

use PDO;
use RedCrossQuest\Service\Logger;

require '../../vendor/autoload.php';

abstract class DBService
{
  /** @var PDO */
  protected $db;
  /** @var Logger */
  protected $logger;

  public function __construct(PDO $db, Logger $logger)
  {
    $this->db     = $db;
    $this->logger = $logger;
  }

}
