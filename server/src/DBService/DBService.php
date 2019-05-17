<?php
namespace RedCrossQuest\DBService;

use RedCrossQuest\Service\Logger;

require '../../vendor/autoload.php';

abstract class DBService
{
  /** @var \PDO */
  protected $db;
  /** @var Logger */
  protected $logger;

  public function __construct($db, $logger)
  {
    $this->db     = $db;
    $this->logger = $logger;
  }

}
