<?php

use Slim\App;


/**
 * get the Path prefix
 * local dev : no prefix (and GAE Flex)
 * GAE Standard : /rest
 * @param bool for authentication, we need an extra / in the prefix
 * @return string the prefix
 */
function getPrefix($authCheck=false)
{
  return "";//isGAE() ? "/rest".($authCheck?"/":""):"/rest";
}

/**
 * if the application is deployed on Google App Engine, it returns true.
 * @return bool
 */
function isGAE()
{
  return array_key_exists('GAE_RUNTIME', $_SERVER );
}



return function (App $app) {

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

};
