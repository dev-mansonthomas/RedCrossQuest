<?php
/**
 * Created by IntelliJ IDEA.
 * User: thomas
 * Date: 2019-01-23
 * Time: 11:09
 */

namespace RedCrossQuest\Service;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Validation;


class ClientInputValidator
{
  /** @var string */
  public static $EMAIL_VALIDATION="email";
  /** @var string */
  public static $UUID_VALIDATION="uuid";

  /** @var string */
  public static $STRING_VALIDATION ="validateString";
  /** @var string */
  public static $INTEGER_VALIDATION="validateInteger";
  /** @var string */
  public static $BOOLEAN_VALIDATION="validateBoolean";


  /** @var LoggerInterface */
  protected $logger;

  public function __construct(LoggerInterface $logger)
  {
    $this->logger         = $logger;
  }

  /**
   * @param  string $parameterName   the name of the input param (for logging purpose)
   * @param  array  $inputArray      The reference to the array containing the $parameterName as a key (or not)
   * @param  int    $maxLength       the max length of the string
   * @param  bool   $notNull         Is the value allowed to be null or not
   * @param  string $validationType  Type of validation (UUID, EMAIL)
   * @return string The trimmed value
   */

  public function validateString(string $parameterName, ?array &$inputArray, int $maxLength, bool $notNull, $validationType=null):?string
  {
    if($inputArray == null || !array_key_exists($parameterName, $inputArray))
    {
      $inputValue = null;
    }
    else
    {
      $inputValue = $inputArray[$parameterName];
    }

    if(!$notNull && $inputValue === null)
      return (string) null;

    $validator = Validation::createValidator();

    $validators = [
      new Length(['max' => $maxLength])
    ];

    if($notNull)
    {
      $validators[count($validators)] = new NotBlank();
    }

    if($validationType == ClientInputValidator::$EMAIL_VALIDATION)
    {
      $validators[count($validators)] = new Email(['mode'=>'strict']);
    }

    if($validationType == ClientInputValidator::$UUID_VALIDATION)
    {
      $validators[count($validators)] = new Uuid(['strict' => true]);
    }

    $violations = $validator->validate($inputValue, $validators);

    if (0 !== count($violations))
    {
      $this->logger->error("Input value fails validations", array(
        "parameterName" => $parameterName,
        "maxLength"     => $maxLength,
        "notNull"       => $notNull,
        "validationType"=> $validationType,
        "violations"    => $violations,
        "inputValue"    => $inputValue));
      throw new InvalidArgumentException("Input value fails string validations. parameterName='$parameterName', inputValue='$inputValue'" );
    }
//trim(htmlentities($inputValue, ENT_QUOTES | ENT_HTML5, "UTF-8"));
    //issue with email address where it breaks the validation.
    return trim($inputValue);
  }

  /**
   * @param  string $parameterName   the name of the input param (for logging purpose)
   * @param  array  $inputArray      The reference to the array containing the $parameterName as a key (or not)
   * @param  int    $maxValue        the max value for the input
   * @param  bool   $notNull         Is the value allowed to be null or not
   * @param  int    $defaultValue    If the value is null, return this value instead.
   * @return int|null The passed value casted to int
   */
  public function validateInteger($parameterName, ?array &$inputArray, $maxValue=0, $notNull=true, $defaultValue=0):?int
  {
    if($inputArray == null || !array_key_exists($parameterName, $inputArray))
    {
      $inputValue = null;
    }
    else
    {
      $inputValue = $inputArray[$parameterName];
    }

    if(!$notNull && $inputValue === null)
      return $defaultValue;

    if("$inputValue"==="0")
      $inputValue = 0;

    $validator = Validation::createValidator();

    $validators = [
      new Type(['type' => 'numeric'])
    ];

    if($notNull)
    {
      $validators[count($validators)] = new NotBlank();
    }

    $rangeCheck = ['min'=>0];

    if(is_int($maxValue) && $maxValue >0)
    {
      $rangeCheck['max'] = $maxValue;
    }

    $validators[count($validators)] = new Range($rangeCheck);


    $violations = $validator->validate($inputValue, $validators);

    if (0 !== count($violations))
    {
      $this->logger->error("Input value fails validations", array(
        "parameterName" => $parameterName,
        "maxValue"      => $maxValue,
        "notNull"       => $notNull,
        "violations"    => $violations,
        "inputValue"    => $inputValue));
      throw new InvalidArgumentException("Input value fails int validations");
    }
    return (int) $inputValue;
  }


  /**
   * @param  string $parameterName  the name of the input param (for logging purpose)
   * @param  array  $inputArray      The reference to the array containing the $parameterName as a key (or not)
   * @param  bool   $notNull        If true, the value can't be null
   * @param  bool   $defaultValue   If the value is null and it's allowed ($notNull=true), then the function will return this bool value instead of null
   * @return boolean true or false
   */
  public function validateBoolean($parameterName,  ?array &$inputArray, bool $notNull, bool $defaultValue=null):?bool
  {
    if($inputArray == null || !array_key_exists($parameterName, $inputArray))
    {
      $inputValue = null;
    }
    else
    {
      $inputValue = $inputArray[$parameterName];
    }

    if(!$notNull && $inputValue === null)
      return (bool)$defaultValue;

    if($inputValue === "1" || $inputValue === 1 || $inputValue === true)
    {
      $inputValue = "true";
    }
    else if($inputValue === "0" || $inputValue === 0 || $inputValue === false)
    {
      $inputValue = "false";
    }


    $validator = Validation::createValidator();

    $validators = [
      new IdenticalTo(['value' => 'true'])
    ];

    $violations = $validator->validate($inputValue, $validators);

    if (0 === count($violations))
    {

      return true;
    }

    $validator = Validation::createValidator();

    $validators = [
      new IdenticalTo(['value' => 'false'])
    ];

    $violations = $validator->validate($inputValue, $validators);

    if (0 === count($violations))
    {
      return false;
    }

    $this->logger->error("Input value fails validations", array(
      "parameterName" => $parameterName,
      "violations"    => $violations,
      "inputValue"    => $inputValue));
    throw new InvalidArgumentException("Input value fails int validations");

  }

  /**
   * Perform the validation of the data according ot the $clientInputValidatorInput
   * @param ClientInputValidatorSpecs $clientInputValidatorInput
   * @return bool|int|string
   */
  public function validate(ClientInputValidatorSpecs $clientInputValidatorInput)
  {
    $methodName = $clientInputValidatorInput->methodName;

    if($methodName != ClientInputValidator::$STRING_VALIDATION  &&
       $methodName != ClientInputValidator::$INTEGER_VALIDATION &&
       $methodName != ClientInputValidator::$BOOLEAN_VALIDATION )
    {
      $this->logger->error("Run check fails, invalid method name", array("methodName" => $methodName));
      throw new InvalidArgumentException("Run check fails, invalid method name");
    }

    switch ($methodName)
    {
      case ClientInputValidator::$STRING_VALIDATION  :
        return $this->validateString ($clientInputValidatorInput->parameterName, $clientInputValidatorInput->inputArray, $clientInputValidatorInput->maxLength, $clientInputValidatorInput->notNull, $clientInputValidatorInput->validationType);
      case  ClientInputValidator::$INTEGER_VALIDATION:
        return $this->validateInteger($clientInputValidatorInput->parameterName, $clientInputValidatorInput->inputArray, $clientInputValidatorInput->maxValue , $clientInputValidatorInput->notNull, $clientInputValidatorInput->defaultValue);
      case ClientInputValidator::$BOOLEAN_VALIDATION :
        return $this->validateBoolean($clientInputValidatorInput->parameterName, $clientInputValidatorInput->inputArray, $clientInputValidatorInput->notNull, $clientInputValidatorInput->defaultValue);
    }

    throw new InvalidArgumentException("Incorrect method name : '$methodName' see static attribute of class ClientInputValidator");
  }
}
