<?php

declare(strict_types=1);

// Make sure the event ID was passed
if (isset($_GET['eventId'])) {
  // Make sure it is an integer
  $id = preg_replace('/[^0-9]/', '', $_GET['eventId']);

  // If the ID isn't valid, send the user to the main page

  if (empty($id)) {
    header("Location: ./");
    exit;
  }
} else {
  // Send the user to the main page if no ID was supplied
  header("Location: ./");
  exit;
}

// Include necessary files
include_once '../sys/core/init.inc.php';

// Output the header
$pageTitle = "View Event";
$cssFiles = ['style.css', 'admin.css'];
include_once 'assets/common/header.inc.php';

//Load the calendar
$calendar = new Calendar($dbo);
?>

<div id="content">
  <?= $calendar->displayEvent($id) ?>

  <a href="./">&laquo; Back to the calendar</a>
</div><!-- end #content -->

<?php
// Output the footer
include_once 'assets/common/footer.inc.php';

?>