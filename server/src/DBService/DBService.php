<?php
namespace RedCrossQuest\DBService;

use Exception;
use PDO;
use RedCrossQuest\Service\Logger;

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

  /**
   * Remove the SELECT part and replace it by a select count(1), to get the number of matching rows for the query
   * @param string $sql the sql query
   * @param array  $parameters the sql query parameters
   * @return int the number of rows
   * @throws Exception
   */
  protected function getCountForSQLQuery(string $sql, array $parameters):int
  {
    try
    {
      $sqlCount = "select count(1) as row_count \nFROM ". explode("FROM", $sql, 2)[1];

      $stmt = $this->db->prepare($sqlCount);
      $stmt->execute($parameters);
      $row = $stmt->fetch();

      return (int) $row['row_count'];
    }
    catch(Exception $e)
    {
      $this->logger->error("error while executeQueryForArray", ["sql"=>$sql, "parameters"=>$parameters, Logger::$EXCEPTION=>$e]);
      throw $e;
    }
  }

  /**
   * Execute the SQL query with the parameters. If rowsPerPage is > 0, add a LIMIT statement to the sql Query.
   * Return an array of object, mapped by the $mappingCallBack function
   * @param string $sql
   * @param array $parameters
   * @param $mappingCallBack
   * @param int $pageNumber
   * @param int $rowsPerPage
   * @return array
   * @throws Exception
   */
  protected function executeQueryForArray(string $sql, array $parameters, $mappingCallBack, ?int $pageNumber=1, ?int $rowsPerPage=0):array
  {
    try
    {
      if($rowsPerPage>0)
      {
        if($pageNumber <1)
          $pageNumber = 1;

        //https://stackoverflow.com/questions/10014147/limit-keyword-on-mysql-with-prepared-statement
        $sql = $sql.sprintf("\nLIMIT %d, %d", (($pageNumber-1)*$rowsPerPage), $rowsPerPage);
      }

      $stmt   = $this->db->prepare($sql);

      $stmt->execute($parameters);

      $results = [];
      $i       = 0;
      while ($row = $stmt->fetch())
      {
        $results[$i++] = $mappingCallBack($row, $this->logger);
      }
      $stmt->closeCursor();
      return $results;

    }
    catch(Exception $e)
    {
      $this->logger->error("error while executeQueryForArray", ["sql"=>$sql, "parameters"=>$parameters, "pageNumber"=>$pageNumber, "rowsPerPage"=>$rowsPerPage, Logger::$EXCEPTION=>$e]);
      throw $e;
    }
  }

  /**
   * Execute the SQL query with the parameters.
   * The query should return 0 or 1 row.
   * When one row is returned, the $mappingCallBack is used to instantiate an object from the row data
   *
   * @param string $sql
   * @param array $parameters
   * @param $mappingCallBack
   * @param bool $throwExceptionIfNotFound if true, if the query returns no row, an exception is thrown
   * @return object instantiated by the callback $mappingCallBack
   * @throws Exception
   */
  protected function executeQueryForObject(string $sql, array $parameters, $mappingCallBack, ?bool $throwExceptionIfNotFound=false):?object
  {
    try
    {
      $stmt   = $this->db->prepare($sql);
      $stmt->execute($parameters);

      if($row = $stmt->fetch())
      {
        return $mappingCallBack($row, $this->logger);
      }
      $stmt->closeCursor();

    }
    catch(Exception $e)
    {
      $this->logger->error("error while executeQueryForObject", ["sql"=>$sql, "parameters"=>$parameters, Logger::$EXCEPTION=>$e]);
      throw $e;
    }
    $message = "SQL query return 0 rows instead of 1";
    if($throwExceptionIfNotFound)
    {
      $this->logger->error($message, ["sql"=>$sql, "parameters"=>$parameters]);
      throw new Exception($message);
    }
    else
    {
      $this->logger->warning($message, ["sql"=>$sql, "parameters"=>$parameters]);
      return null;
    }
  }


  /**
   * Execute the SQL query with the parameters to insert a row.
   *
   * if $returnLastInsertedId is set to true, the query is executed in a transaction and the last inserted id is returned
   *
   * @param string $sql
   * @param array $parameters
   * @param bool $returnLastInsertedId if true, if the query returns no row, an exception is thrown
   * @return int the last_inserted_id if requested by setting $returnLastInsertedId to true
   * @throws Exception
   */
  protected function executeQueryForInsert(string $sql, array $parameters, ?bool $returnLastInsertedId=false):?int
  {
    try
    {
      if($returnLastInsertedId)
      {
        $stmt = $this->db->prepare($sql);

        //sometime insert operation are within a more complex operation that requires transaction
        //in this case, we don't start or commit the transaction)
        $transactionCurrentlyInProgress = false;
        if(!$this->db->inTransaction())
        {
          $this->db->beginTransaction();
        }
        else
        {
          $transactionCurrentlyInProgress = true;
        }

        $stmt->execute($parameters);

        $stmt->closeCursor();

        $stmt = $this->db->query("select last_insert_id()");
        $row  = $stmt->fetch();

        $lastInsertId = $row['last_insert_id()'];

        $stmt->closeCursor();
        if(!$transactionCurrentlyInProgress)
        {
          $this->db->commit();
        }

        return $lastInsertId;
      }
      else
      {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parameters);
        $stmt->closeCursor();
        return null;
      }
    }
    catch(Exception $e)
    {
      $this->logger->error("error while inserting a row", ["sql"=>$sql, "parameters"=>$parameters, 'returnLastInsertedId'=>$returnLastInsertedId, Logger::$EXCEPTION=>$e]);
      throw $e;
    }
  }


  /**
   * Execute the SQL query with the parameters to update a row.
   * 
   * @param string $sql
   * @param array $parameters
   * @return int the number of rows updated
   * @throws Exception
   */
  protected function executeQueryForUpdate(string $sql, array $parameters):int
  {
    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->execute($parameters);
      $updatedRows = $stmt->rowCount();
      $stmt->closeCursor();
    }
    catch(Exception $e)
    {
      $this->logger->error("error while updating a row", ["sql"=>$sql, "parameters"=>$parameters, Logger::$EXCEPTION=>$e]);
      throw $e;
    }
    return $updatedRows;
  }

  /**
   * Start a transaction on the DB
   *
   * In some case, a service wants to coordinate multiple updates on several sql update/insert on one or several tables.
   * That's why this method is public
   */
  public function transactionStart()
  {
    $this->db->beginTransaction();
  }
  /**
   * Commit a transaction on the DB
   *
   * In some case, a service wants to coordinate multiple updates on several sql update/insert on one or several tables.
   * That's why this method is public
   */
  public function transactionCommit()
  {
    $this->db->commit();
  }
  /**
   * Rollback a transaction on the DB
   *
   * In some case, a service wants to coordinate multiple updates on several sql update/insert on one or several tables.
   * That's why this method is public
   */
  public function transactionRollback()
  {
    $this->db->rollBack();
  }


}
