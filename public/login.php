<?php

declare(strict_types=1);

// Include necessary files
include_once '../sys/core/init.inc.php';

// Set up the page title and CSS files
$pageTitle = 'Events Calendar';
$cssFiles = ['style.css', 'admin.css'];

// Output the header
include 'assets/common/header.inc.php'
?>

<div id="content">
  <form action="assets/inc/process.inc.php" method="post">
    <fieldset>
      <legend>Please log in</legend>
      <label for="username">Username</label>
      <input type="text" name="username" id="username">
      <label for="password">Password</label>
      <input type="password" name="password" id="password">
      <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
      <input type="hidden" name="action" value="userLogin">
      <input type="submit" value="Log In" name="loginSubmit"> or <a href="./">cancel</a>
    </fieldset>
  </form>
</div><!-- end #content -->

<?php
// output the footer
include 'assets/common/footer.inc.php'
?>