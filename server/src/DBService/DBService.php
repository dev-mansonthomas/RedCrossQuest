<?php
namespace RedCrossQuest\DBService;

abstract class DBService
{
    protected $db;
    protected $logger;

    public function __construct($db, $logger)
    {
      $this->db     = $db;
      $this->logger = $logger;
    }

}
