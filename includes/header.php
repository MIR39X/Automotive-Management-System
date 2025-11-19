<?php
$base = '/ams_project'; // change if folder name differs
$currentPage = basename($_SERVER['PHP_SELF']);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$isAuthenticated = !empty($_SESSION['user']);

if (!empty($requireAuth) && !$isAuthenticated) {
  $redirectTarget = $base . '/public/login.php';
  if (!empty($_SERVER['REQUEST_URI'])) {
    $redirectTarget .= '?redirect=' . urlencode($_SERVER['REQUEST_URI']);
  }
  header("Location: $redirectTarget");
  exit;
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>AMS - Automotive Management System</title>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
  <style>
    .logo {
      display:flex;
      align-items:center;
      gap:12px;
    }
    .logo-badge {
      width:68px;
      height:58px;
      border-radius:18px;
      background:linear-gradient(135deg,#1d4ed8,#9333ea);
      color:#fff;
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      letter-spacing:0.08em;
      font-size:16px;
      box-shadow:0 12px 25px rgba(79,70,229,0.35);
    }
    .nav {
      margin-left:auto;
      display:flex;
      align-items:center;
      gap:18px;
      background:linear-gradient(145deg,#f8fafc,#eef2ff);
      padding:10px 18px;
      border-radius:18px;
      border:1px solid #e2e8f0;
      box-shadow:0 10px 25px rgba(15,23,42,0.08);
    }
    .nav a {
      text-decoration:none;
      color:#475569;
      font-weight:500;
      padding:8px 12px;
      border-radius:12px;
      transition:color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
    }
    .nav a:hover {
      color:#1d4ed8;
      background:rgba(37,99,235,0.08);
    }
    .nav a.active {
      background:#dbeafe;
      color:#1d4ed8;
      box-shadow:0 6px 16px rgba(37,99,235,0.25);
    }
    @media (max-width:768px) {
      .nav {
        flex-wrap:wrap;
        justify-content:center;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div class="header" style="flex:1">
        <div class="logo">
          <div class="logo-badge" aria-hidden="true">AMS</div>
          <div class="title">
            <div class="brand">AMS - Automotive Management</div>
            <div class="meta">Simple demo - Local dev</div>
          </div>
        </div>
        <nav class="nav" aria-label="Main navigation">
          <a href="<?= $base ?>/public/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
          <?php if ($isAuthenticated): ?>
            <a href="<?= $base ?>/public/vehicles/list.php" class="<?= $currentPage === 'list.php' && strpos($_SERVER['REQUEST_URI'], 'vehicles') !== false ? 'active' : '' ?>">Vehicles</a>
            <a href="<?= $base ?>/public/employees/list.php" class="<?= strpos($_SERVER['REQUEST_URI'], 'employees') !== false ? 'active' : '' ?>">Employees</a>
            <a href="<?= $base ?>/public/customers/list.php" class="<?= strpos($_SERVER['REQUEST_URI'], 'customers') !== false ? 'active' : '' ?>">Customers</a>
            <a href="<?= $base ?>/public/logout.php">Logout</a>
          <?php else: ?>
            <a href="<?= $base ?>/public/login.php" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>">Login</a>
          <?php endif; ?>
        </nav>
      </div>
    </div>
