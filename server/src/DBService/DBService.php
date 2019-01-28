<?php
namespace RedCrossQuest\DBService;

require '../../vendor/autoload.php';

abstract class DBService
{
  /** @var \PDO */
  protected $db;
  /** @var \Google\Cloud\Logging\PsrLogger */
  protected $logger;

  public function __construct($db, $logger)
  {
    $this->db     = $db;
    $this->logger = $logger;
  }

}
