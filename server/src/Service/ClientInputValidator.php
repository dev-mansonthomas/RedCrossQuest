<?php
/**
 * Created by IntelliJ IDEA.
 * User: thomas
 * Date: 2019-01-23
 * Time: 11:09
 */

namespace RedCrossQuest\Service;

use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Range;



class ClientInputValidator
{

  public static $EMAIL_VALIDATION="email";
  public static $UUID_VALIDATION="uuid";

  /** @var Logger */
  protected $logger;

  public function __construct($logger)
  {
    $this->logger         = $logger;
  }

  /**
   * @param  string $parameterName   the name of the input param (for logging purpose)
   * @param  string $inputValue      the value to validate
   * @param  int    $maxLength       the max length of the string
   * @param  bool   $notNull         Is the value allowed to be null or not
   * @param  string $validationType  Type of validation (UUID, EMAIL)
   * @return string The trimmed value
   */

  public function validateString(string $parameterName, ?string $inputValue, int $maxLength, bool $notNull, $validationType=null)
  {

    if(!$notNull && $inputValue == null)
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
      throw new \InvalidArgumentException("Input value fails string validations. parameterName='$parameterName', inputValue='$inputValue'" );
    }
//trim(htmlentities($inputValue, ENT_QUOTES | ENT_HTML5, "UTF-8"));
    //issue with email address where it breaks the validation.
    return trim($inputValue);
  }

  /**
   * @param  string $parameterName   the name of the input param (for logging purpose)
   * @param  string $inputValue      the value to validate
   * @param  int    $maxValue        the max value for the input
   * @param  bool   $notNull         Is the value allowed to be null or not
   * @param  int    $defaultValue    If the value is null, return this value instead.
   * @return int The passed value casted to int
   */
  public function validateInteger($parameterName, $inputValue, $maxValue=0, $notNull=true, $defaultValue=0)
  {
    if(!$notNull && $inputValue == null)
      return $defaultValue;

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
      throw new \InvalidArgumentException("Input value fails int validations");
    }
    return (int) $inputValue;
  }


  /**
   * @param  string $parameterName        the name of the input param (for logging purpose)
   * @param  string $inputValue           the value to validate
   * @param  bool   $notNull              If true, the value can't be null
   * @param  bool   $defaultValueWhenNull If the value is null and it's allowed ($notNull=true), then the function will return this bool value instead of null
   * @return boolean true or false
   */
  public function validateBoolean($parameterName, $inputValue, bool $notNull, bool $defaultValueWhenNull=null)
  {

    if(!$notNull && $defaultValueWhenNull === null)
      throw new \InvalidArgumentException("Coding error, value can be null, but no default value is given. parameterName='$parameterName', inputValue='$inputValue', notNull='$notNull' defaultValueWhenNull='$defaultValueWhenNull'");

    if(!$notNull && $inputValue == null)
      return (bool)$defaultValueWhenNull;

    if($inputValue === "1" || $inputValue === 1)
    {
      $inputValue = "true";
    }
    else if($inputValue === "0" || $inputValue === 0)
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
    throw new \InvalidArgumentException("Input value fails int validations");

  }

}