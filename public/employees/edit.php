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
  header('Location: list.php');
  exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $dob = $_POST['dob'] ?? null;
  $cnic = trim($_POST['cnic'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $join_date = $_POST['join_date'] ?? null;
  $salary = floatval($_POST['salary'] ?? 0);
  $work_hours = intval($_POST['work_hours'] ?? 0);

  if ($name === '') $errors[] = 'Name is required';
  if ($dob === null) $errors[] = 'Date of birth is required';
  if ($cnic === '') $errors[] = 'CNIC is required';
  if ($address === '') $errors[] = 'Address is required';
  if ($join_date === null) $errors[] = 'Join date is required';
  if ($salary <= 0) $errors[] = 'Salary must be greater than 0';
  if ($work_hours <= 0) $errors[] = 'Work hours must be greater than 0';

  if (empty($errors)) {
    $stmt = $pdo->prepare("UPDATE employee SET name=?, dob=?, cnic=?, address=?, join_date=?, salary=?, work_hours=? WHERE id=?");
    $stmt->execute([$name, $dob, $cnic, $address, $join_date, $salary, $work_hours, $id]);
    header("Location: view.php?id=$id");
    exit;
  }
} else {
  $_POST = $employee;
}
?>

<div class="page-hero" style="display:flex;align-items:center;justify-content:space-between">
  <h2>Edit Employee</h2>
  <a class="btn" href="view.php?id=<?=$id?>">View profile</a>
</div>

<div class="card">
  <?php if (!empty($errors)): ?>
    <div style="color:#b91c1c;margin-bottom:10px">
      <?php foreach ($errors as $e): ?><?=htmlspecialchars($e) . '<br>'?><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="form-row"><label>Name*</label><input type="text" name="name" required value="<?=htmlspecialchars($_POST['name'] ?? '')?>"></div>
    <div class="form-row"><label>Date of Birth*</label><input type="date" name="dob" required value="<?=htmlspecialchars($_POST['dob'] ?? '')?>"></div>
    <div class="form-row"><label>CNIC*</label><input type="text" name="cnic" required value="<?=htmlspecialchars($_POST['cnic'] ?? '')?>"></div>
    <div class="form-row"><label>Address*</label><textarea name="address" required><?=htmlspecialchars($_POST['address'] ?? '')?></textarea></div>
    <div class="form-row"><label>Join Date*</label><input type="date" name="join_date" required value="<?=htmlspecialchars($_POST['join_date'] ?? '')?>"></div>
    <div class="form-row"><label>Salary*</label><input type="number" name="salary" step="0.01" required value="<?=htmlspecialchars($_POST['salary'] ?? '')?>"></div>
    <div class="form-row"><label>Work Hours*</label><input type="number" name="work_hours" required value="<?=htmlspecialchars($_POST['work_hours'] ?? '')?>"></div>

    <div style="margin-top:12px"><button class="btn" type="submit">Save changes</button> <a href="view.php?id=<?=$id?>" style="margin-left:10px;color:#6b7280">Cancel</a></div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>