<?php
$base = '/ams_project'; // change if folder name differs
$currentPage = basename($_SERVER['PHP_SELF']);
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>AMS - Automotive Management System</title>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div class="header" style="flex:1">
        <div class="logo">
          <!-- optional small logo image (place logo.png in assets) -->
          <img src="<?= $base ?>/assets/logo.png" alt="" onerror="this.style.display='none'">
          <div class="title">
            <div class="brand">AMS — Automotive Management</div>
            <div class="meta">Simple demo • Local dev</div>
          </div>
        </div>
        <nav class="nav" aria-label="Main navigation" style="margin-left:auto">
          <a href="<?= $base ?>/public/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
          <a href="<?= $base ?>/public/vehicles/list.php" class="<?= $currentPage === 'list.php' ? 'active' : '' ?>">Vehicles</a>
          <a href="<?= $base ?>/public/customers/list.php" class="<?= $currentPage === 'customers.php' ? 'active' : '' ?>">Customers</a>
          <a href="<?= $base ?>/public/sales/list.php" class="<?= $currentPage === 'sales.php' ? 'active' : '' ?>">Sales</a>
          <a href="<?= $base ?>/public/maintenance/list.php" class="<?= $currentPage === 'maintenance.php' ? 'active' : '' ?>">Maintenance</a>
          <a href="<?= $base ?>/public/login.php" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>">Login</a>
        </nav>
      </div>
    </div>
