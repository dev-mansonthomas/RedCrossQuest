<?php /** @noinspection PhpUnusedParameterInspection */

use Slim\App;

return function (App $app) {
//$app is used in the include, do not delete
// Routes
  include_once("routes/00-authentication.php");
  include_once("routes/01-troncs.php");
  include_once("routes/02-troncs-queteurs.php");
  include_once("routes/03-points-quetes.php");
  include_once("routes/04-queteurs.php");
  include_once("routes/05-dailyStats.php");
  include_once("routes/06-users.php");
  include_once("routes/07-spotfire-access.php");
  include_once("routes/08-troncs-queteurs-history.php");
  include_once("routes/09-settings.php");
  include_once("routes/10-unite-locale.php");
  include_once("routes/11-yearly-goals.php");
  include_once("routes/12-named_donation.php");
  include_once("routes/13-mailing.php");
  include_once("routes/14-exportData.php");
  include_once("routes/15-money-bag.php");
  include_once("routes/16-unite-locale-registration.php");
};
