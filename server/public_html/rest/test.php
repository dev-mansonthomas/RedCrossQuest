<?php
echo "getEnv MYSQL_DSN<br/>";
echo getenv('MYSQL_DSN');
echo "<br/>";
$value = "";
$test = ($value."" === "1" || $value."" === "true");

echo "value='$value' test='$test'<br/>";

if($test)
{
  echo "true";
}
else
{
  echo "false";
}






//date_default_timezone_set("Europe/Paris");
//  echo date_format(new DateTime('now'), 'Y-m-d T');

