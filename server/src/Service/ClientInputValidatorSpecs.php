<?php


namespace RedCrossQuest\Service;


class ClientInputValidatorSpecs
{
  /**
   * @var string
   */
  public $methodName;
  /**
   * @var string
   */
  public $parameterName;
  /**
   * @var string
   */
  public $inputValue;
  /**
   * @var int
   */
  public $maxLength;
  /**
   * @var bool
   */
  public $notNull;
  /**
   * @var string
   */
  public $validationType;
  /**
   * @var string
   */
  public $defaultValue;
  /**
   * @var string
   */
  public $maxValue;


  public function __construct(string $methodName, string $parameterName, bool $notNull, ?string $inputValue)
  {
    $this->methodName     = $methodName;
    $this->parameterName  = $parameterName;
    $this->notNull        = $notNull;
    $this->inputValue     = $inputValue;
  }

  public static function withString(string $parameterName, ?string $inputValue, int $maxLength, bool $notNull, ?string $validationType=null)
  {
    $instance = new self(ClientInputValidator::$STRING_VALIDATION, $parameterName, $notNull, $inputValue);
    $instance->maxLength      = $maxLength;
    $instance->validationType = $validationType;
    return $instance;
  }

  public static function withInteger(string $parameterName, ?string $inputValue, int $maxValue, bool $notNull,  ?int $defaultValue=null)
  {
    $instance = new self(ClientInputValidator::$INTEGER_VALIDATION, $parameterName, $notNull, $inputValue);
    $instance->maxValue     = $maxValue;
    $instance->defaultValue = $defaultValue;
    return $instance;
  }

  public static function withBoolean(string $parameterName, ?string $inputValue, bool $notNull,  ?bool $defaultValue=null)
  {
    $instance = new self(ClientInputValidator::$BOOLEAN_VALIDATION, $parameterName, $notNull, $inputValue);
    $instance->defaultValue = $defaultValue;
    return $instance;
  }

}
