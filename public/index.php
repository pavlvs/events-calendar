<?php

declare(strict_types=1);

// Include necessary files
include_once '../sys/core/init.inc.php';

// Load the calendar for January
$calendar = new Calendar($dbo, "2020-01-01 12:00:00");

// Set up the page title and CSS files
$pageTitle = 'Events Calendar';
$cssFiles = ['style.css', 'admin.css', 'ajax.css'];

include 'assets/common/header.inc.php'
?>

<div id="content">
  <!-- Display the calendar HTML -->
  <?= $calendar->buildCalendar(); ?>
</div><!-- end #content -->
<p>
  <?= isset($_SESSION['user']) ? 'Logged In!' : 'Logged Out!' ?>
</p>

<?php include 'assets/common/footer.inc.php' ?>