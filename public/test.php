<?php

declare(strict_types=1);

// Include necessary files
include_once '../sys/core/init.inc.php';

// Load the calendar for January
$obj = new Admin($dbo);

$pass = $obj->testSaltedHash('admin');
echo "Hash of admin:<br>", $pass . "<br><br>";


$hash1 = $obj->testSaltedHash('test');
echo "Hash 1 without a salt:<br>", $hash1 . "<br><br>";

sleep(1);


$hash2 = $obj->testSaltedHash('test');
echo "Hash 2 without a salt:<br>", $hash2 . "<br><br>";

sleep(1);

$hash3 = $obj->testSaltedHash('test', $hash2);
echo "Hash 3 with the salt from hash 2:<br>", $hash3 . "<br><br>";
