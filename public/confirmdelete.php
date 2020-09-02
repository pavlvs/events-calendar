<?php

declare(strict_types=1);

//Enable sessions if needed
$status = session_status();

if ($status == PHP_SESSION_NONE) {
  // There is no active session
  session_start();
}
// Make sure the event ID was passed and the user is logged in
if (isset($_POST['eventId']) && isset($_SESSION['user'])) {
  // Collect the event ID from the URL string
  $id = (int)$_POST['eventId'];
} else {
  // Send the user to the main page if no ID is supplied
  // or the user is not logged in
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