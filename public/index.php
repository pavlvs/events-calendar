<?php

declare(strict_types=1);

// Include necessary files
include_once '../sys/core/init.inc.php';

// Load the calendar for January
$calendar = new Calendar($dbo, "2020-01-01 12:00:00");

// display the calendar HTML


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events Calendar</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <div id="content">
    <?= $calendar->buildCalendar(); ?>

  </div>

</body>

</html>