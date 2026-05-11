<?php


namespace RedCrossQuest\Service;


class ClientInputValidatorSpecs
{
  /**
   * @var string
   */
  public string $methodName;
  /**
   * @var string
   */
  public string $parameterName;
  /**
   * @var array|null that contains $parameterName as key to get the value that will be validated.
   *      Nullable because Slim's getParsedBody() / getQueryParams() can return null when the
   *      request has no body or a content-type that no body parser handled. ClientInputValidator
   *      already handles a null $inputArray downstream (treats every key as missing), and the
   *      `notNull` flag on each spec then triggers a proper InvalidArgumentException -> 400.
   *      Typing it as non-nullable `array` caused a TypeError on PHP 8 ("Cannot assign null to
   *      property ... of type array") on every empty-body POST (legitimate or scanner), which
   *      bubbled up as a 500 + Slack alert instead of the intended 400.
   */
  public ?array $inputArray;
  /**
   * @var int
   */
  public int $maxLength;
  /**
   * @var bool
   */
  public bool $notNull;
  /**
   * @var string | null
   */
  public ?string $validationType=null;
  /**
   * @var int|string|null
   */
  public int|string|null $defaultValue;
  /**
   * @var string
   */
  public string $maxValue;


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
