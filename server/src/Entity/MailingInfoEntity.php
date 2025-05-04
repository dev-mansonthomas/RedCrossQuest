<?php
namespace RedCrossQuest\Entity;

use Exception;
use Psr\Log\LoggerInterface;

class MailingInfoEntity extends Entity
{
  public int $id;
  public string $email                       ;
  public string $first_name                  ;
  public string $last_name                   ;
  /**
  "1">Action Sociale
  "2">Secours
  "3">Non Bénévole
  "4">Ancien Bénévole, Inactif ou Adhérent
  "5">Commerçant
  "6">Special
   */
  public int $secteur                     ;
  public bool $man                         ;
  public string $spotfire_access_token       ;

  // not retrieved from DB

  public int $status                      ;

  protected array $_fieldList = ['id', 'email', 'first_name', 'last_name', 'secteur', 'man', 'spotfire_access_token', 'status'];

  /**
   * Accept an array of data matching properties of this class
   * and create the class
   *
   * @param array $data The data to use to create
   * @param LoggerInterface $logger
   * @throws Exception if a parse Date or JSON fails
   */
  public function __construct(array &$data, LoggerInterface $logger)
  {
    parent::__construct($logger);
    $this->getInteger('id'                          , $data);
    $this->getEmail  ('email'                       , $data);
    $this->getString ('first_name'                  , $data, 100);
    $this->getString ('last_name'                   , $data, 100);
    $this->getInteger('secteur'                     , $data);
    $this->getBoolean('man'                         , $data);
    $this->getString ('spotfire_access_token'       , $data, 36);
  }
}
