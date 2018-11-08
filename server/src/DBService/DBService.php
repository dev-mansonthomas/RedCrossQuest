<?php
namespace RedCrossQuest\DBService;

abstract class DBService
{
  /** @var \PDO */
  protected $db;
  /** @var \Monolog\Logger */
  protected $logger;

  public function __construct($db, $logger)
  {
    $this->db     = $db;
    $this->logger = $logger;
  }

}
