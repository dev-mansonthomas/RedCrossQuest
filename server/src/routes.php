<?php

/**
 * @param string $parameterName the name of the parameter in the request
 * @param string $parameter     the parameter value
 * @param int    $maxSize       the max acceptable size of the string
 * @return string the parameter, but trimmed.
 *
 */
function checkStringParameter(string $parameterName, string $parameter, int $maxSize)
{
  $trimmedValue = trim($parameter);

  if( $trimmedValue        == null ||
     strlen($trimmedValue) == 0    ||
     strlen($trimmedValue) >  $maxSize)
  {
    throw new \InvalidArgumentException("parameter '$$parameterName' is invalid");
  }
  return $trimmedValue;
}


// Routes
include_once("../../src/routes/00-authentication.php");
include_once("../../src/routes/01-troncs.php");
include_once("../../src/routes/02-troncs-queteurs.php");
include_once("../../src/routes/03-points-quetes.php");
include_once("../../src/routes/04-queteurs.php");
include_once("../../src/routes/05-dailyStats.php");
include_once("../../src/routes/06-users.php");
include_once("../../src/routes/07-spotfire-access.php");
include_once("../../src/routes/08-troncs-queteurs-history.php");
include_once("../../src/routes/09-settings.php");
include_once("../../src/routes/10-unite-locale.php");
include_once("../../src/routes/11-yearly-goals.php");
include_once("../../src/routes/12-named_donation.php");
include_once("../../src/routes/13-mailing.php");
include_once("../../src/routes/14-exportData.php");

