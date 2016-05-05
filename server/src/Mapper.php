<?php
namespace RedCrossQuest;

abstract class Mapper {
    protected $db;

    public function __construct($db, $logger)
    {
      $this->db     = $db;
      $this->logger = $logger;
    }

}
