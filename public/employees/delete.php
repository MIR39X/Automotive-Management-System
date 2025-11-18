<?php
require_once __DIR__ . '/../../includes/db.php';
$id = intval($_GET['id'] ?? 0);
if ($id) {
  $stmt = $pdo->prepare("DELETE FROM employee WHERE id = ?");
  $stmt->execute([$id]);
}
header('Location: list.php');
exit;