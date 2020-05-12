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
   * @var array that contains $parameterName as key to get the value that will be validated. InputArray can be null or the parameterName not be available as a key in the array
   */
  public $inputArray;
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


  public function __construct(string $methodName, string $parameterName, bool $notNull, ?array &$inputArray)
  {
    $this->methodName     = $methodName;
    $this->parameterName  = $parameterName;
    $this->notNull        = $notNull;
    $this->inputArray     = &$inputArray;
  }

  public static function withString(string $parameterName, ?array &$inputArray, int $maxLength, bool $notNull, ?string $validationType=null):ClientInputValidatorSpecs
  {
    $instance = new self(ClientInputValidator::$STRING_VALIDATION, $parameterName, $notNull, $inputArray);
    $instance->maxLength      = $maxLength;
    $instance->validationType = $validationType;
    return $instance;
  }

  public static function withInteger(string $parameterName, ?array &$inputArray, int $maxValue, bool $notNull,  ?int $defaultValue=null):ClientInputValidatorSpecs
  {
    $instance = new self(ClientInputValidator::$INTEGER_VALIDATION, $parameterName, $notNull, $inputArray);
    $instance->maxValue     = $maxValue;
    $instance->defaultValue = $defaultValue;
    return $instance;
  }

  public static function withBoolean(string $parameterName, ?array &$inputArray, bool $notNull,  ?bool $defaultValue=null):ClientInputValidatorSpecs
  {
    $instance = new self(ClientInputValidator::$BOOLEAN_VALIDATION, $parameterName, $notNull, $inputArray);
    $instance->defaultValue = $defaultValue;
    return $instance;
  }

}
