<?php

declare(strict_types=1);

/**
 * Enable sessions if needed
 * Avoid pesky warning if session already exists
 */

$status = session_status();
if ($status == PHP_SESSION_NONE) {
  //There is no active session
  session_start();
}

/**
 * Generate an anti-CSRF token if one doesn't exist
 */

if (!isset($_SESSION['token'])) {
  $_SESSION["token"] = sha1(uniqid((string)mt_rand(), true));
}

// include the necessary config info
include_once '../sys/config/db-cred.inc.php';

//define constants for configuration file
foreach ($constants as $name => $val) {
  define($name, $val);
}

// Create a PDO object
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$dbo = new PDO($dsn, DB_USER, DB_PASSWORD);

// Define the auto-load function for classes
function __autoload($class)
{
  $filename = '../sys/class/class.' . $class . '.inc.php';
  if (file_exists($filename)) {
    include_once $filename;
  }
}
