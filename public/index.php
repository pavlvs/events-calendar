<?php

declare(strict_types=1);

// Include necessary files
include_once '../sys/core/init.inc.php';

// Load the calendar for January
$calendar = new Calendar($dbo, "2020-01-01 12:00:00");

// display the calendar HTML

echo $calendar->buildCalendar();
