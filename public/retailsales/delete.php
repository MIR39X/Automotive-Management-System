<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
$base = '/ams_project';

if ($requireAuth) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  if (empty($_SESSION['user'])) {
    $redirectTarget = $base . '/public/login.php';
    if (!empty($_SERVER['REQUEST_URI'])) {
      $redirectTarget .= '?redirect=' . urlencode($_SERVER['REQUEST_URI']);
    }
    header("Location: $redirectTarget");
    exit;
  }
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
  // delete details first
  $stmt = $pdo->prepare("DELETE FROM retailsale_details WHERE retailsale_id = ?");
  $stmt->execute([$id]);

  // delete main sale
  $stmt = $pdo->prepare("DELETE FROM retailsale WHERE id = ?");
  $stmt->execute([$id]);
}

header('Location: list.php');
exit;
