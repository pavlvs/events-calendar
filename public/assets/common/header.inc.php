<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <?php foreach ($cssFiles  as $css) : ?>
    <link rel="stylesheet" href="assets/css/<?= $css ?>">
  <?php endforeach; ?>
</head>

<body>