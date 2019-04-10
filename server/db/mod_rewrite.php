<?php
/**
 * @file
 * Provide basic mod_rewrite like functionality.
 *
 * Pass through requests for root php files and forward all other requests to
 * index.php with $_GET['q'] equal to path. The following are examples that
 * demonstrate how a request using mod_rewrite.php will appear to a PHP script.
 *
 * - /install.php: install.php
 * - /update.php?op=info: update.php?op=info
 * - /foo/bar: index.php?q=/foo/bar
 * - /: index.php?q=/
 */



// Provide mod_rewrite like functionality. If a php file in the root directory
// is explicitly requested then load the file, otherwise load index.php and
// set get variable 'q' to $_SERVER['REQUEST_URI'].
//if (dirname($path) == '/' && pathinfo($path, PATHINFO_EXTENSION) == 'php') {
//  $file = pathinfo($path, PATHINFO_BASENAME);
//}
//else {

//}

// Override the script name to simulate the behavior without mod_rewrite.php.
// Ensure that $_SERVER['SCRIPT_NAME'] always begins with a / to be consistent
// with HTTP request and the value that is normally provided.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_SERVER['REQUEST_URI']= "/rest/".$path;
$_SERVER['SCRIPT_NAME']='/rest/index.php';
require "index.php";