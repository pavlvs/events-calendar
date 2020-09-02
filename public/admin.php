<?php

declare(strict_types=1);

// include necessary files

include_once '../sys/core/init.inc.php';

// if the user is not logged in, send them to the main file

if (!isset($_SESSION['user'])) {
  header('Location: ./');
  exit;
}

//Output the header
$pageTitle = 'Add/Edit Event';
$cssFiles = ['style.css', 'admin.css'];
include_once 'assets/common/header.inc.php';

// Load the calendar
$calendar = new Calendar();

?>

<div id="content">
  <?= $calendar->displayForm() ?>
</div><!-- end #content -->

<?php
// Output the footer
include_once 'assets/common/footer.inc.php';
?>