<?php


namespace RedCrossQuest\Exception;


use Throwable;

class UserAlreadyExistsException extends \Exception
{
  public $users;

  public function __construct($message = "", $code = 0, Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}