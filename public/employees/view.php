<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM employee WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();
if (!$employee) {
  echo '<div class="card">Employee not found</div>';
  require_once __DIR__ . '/../../includes/footer.php';
  exit;
}
?>

<div class="page-hero" style="display:flex;align-items:center;justify-content:space-between">
  <h2><?=htmlspecialchars($employee['name'])?></h2>
  <a class="btn" href="list.php">Back to employees</a>
</div>

<div class="card">
  <div><strong>Name:</strong> <?=htmlspecialchars($employee['name'])?></div>
  <div><strong>Date of Birth:</strong> <?=htmlspecialchars($employee['dob'])?></div>
  <div><strong>CNIC:</strong> <?=htmlspecialchars($employee['cnic'])?></div>
  <!-- <div><strong>Address:</strong> <?=nl2br(htmlspecialchars($employee['address']))?></div> -->
  <!-- <div><strong>Join Date:</strong> <?=htmlspecialchars($employee['join_date'])?></div> -->
  <div><strong>Salary:</strong> <?=htmlspecialchars($employee['salary'])?></div>
  <!-- <div><strong>Work Hours:</strong> <?=htmlspecialchars($employee['work_hours'])?></div> -->
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>