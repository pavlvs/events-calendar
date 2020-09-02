<?php

declare(strict_types=1);

// Enable sessions if needed

$status = session_status();
if ($status == PHP_SESSION_NONE) {
    // There is no active session
    session_start();
}

// Include necessary files
include_once '../../../sys/config/db-cred.inc.php';

// define constants for config info

foreach ($constants as $name => $val) {
    define($name, $val);
}

// create a lookup array for form actions

define('ACTIONS', [
    'eventView' => [
        'object' => 'Calendar',
        'method' => 'displayEvent'
    ]
]);


// Need a PDO object
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$dbo = new PDO($dsn, DB_USER, DB_PASSWORD);

//make sure the anti-CSRF token was passed and that the
// requested action exists in the lookup array
if (isset(ACTIONS[$_POST['action']])) {
    $useArray = ACTIONS[$_POST['action']];
    $obj = new $useArray['object']($dbo);
    $method = $useArray['method'];

    // Check for an ID and sanitize it if found
    if (isset($_POST['eventId'])) {
        $id = (int)$_POST['eventId'];
    } else {
        $id = NULL;
    }
    echo $obj->$method($id);
}

function __autoload($className)
{
    $filename = '../../../sys/class/class.' . strtolower($className) . '.inc.php';
    if (file_exists($filename)) {
        include_once $filename;
    }
}
