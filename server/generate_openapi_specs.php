<?php

use function OpenApi\scan;

require("vendor/autoload.php");

$currentDir = dirname(realpath($_SERVER['PHP_SELF']));
$pathToScan = $currentDir."/src/";
$outputPath = $currentDir."/openapi/rcq-openapi.yaml";

$openaAPI = scan($pathToScan);
file_put_contents($outputPath, $openaAPI->toYaml());
