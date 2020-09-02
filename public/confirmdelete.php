<?php

declare(strict_types=1);

// Make sure the event ID was passed
if (isset($_POST['eventId'])) {
  // Collect the event ID from the URL string
  $id = (int)$_POST['eventId'];
} else {
  //Send the user to the main page if no ID is supplied
  header('Location: ./');
  exit;
}

// Include necessary files
include_once '../sys/core/init.inc.php';

// Load the calendar for January
$calendar = new Calendar($dbo, "2020-01-01 12:00:00");
$markup = $calendar->confirmDelete($id);

// Set up the page title and CSS files
$pageTitle = 'Events Calendar';
$cssFiles = ['style.css', 'admin.css'];

// Output the header
include 'assets/common/header.inc.php'
?>

<div id="content">
  <!-- Display the calendar HTML -->
  <?= $markup; ?>
</div><!-- end #content -->

<?php
// Output the footer
include 'assets/common/footer.inc.php'
?>