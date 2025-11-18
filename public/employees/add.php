<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $role = trim($_POST['role'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $hire_date = $_POST['hire_date'] ?? null;
  $dob = $_POST['dob'] ?? null;
  $salary = floatval($_POST['salary'] ?? 0);
  $cnic = trim($_POST['cnic'] ?? '');

  if ($name === '') $errors[] = 'Name is required';
  if ($role === '') $errors[] = 'Role is required';
  if ($phone === '') $errors[] = 'Phone is required';
  if ($hire_date === null) $errors[] = 'Hire date is required';
  if ($dob === null) $errors[] = 'Date of birth is required';
  if ($salary <= 0) $errors[] = 'Salary must be greater than 0';
  if ($cnic === '') $errors[] = 'CNIC is required';

  if (empty($errors)) {
    $stmt = $pdo->prepare("INSERT INTO employee (name, role, phone, hire_date, dob, salary, cnic) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $role, $phone, $hire_date, $dob, $salary, $cnic]);
    header('Location: list.php');
    exit;
  }
}
?>

<div class="page-hero" style="display:flex;align-items:center;justify-content:space-between">
  <h2>Add Employee</h2>
  <a class="btn" href="list.php">Back to employees</a>
</div>

<div class="card">
  <?php if (!empty($errors)): ?>
    <div style="color:#b91c1c;margin-bottom:10px">
      <?php foreach ($errors as $e): ?><?=htmlspecialchars($e) . '<br>'?><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="form-row"><label>Name*</label><input type="text" name="name" required value="<?=htmlspecialchars($_POST['name'] ?? '')?>"></div>
    <div class="form-row"><label>Role*</label><input type="text" name="role" required value="<?=htmlspecialchars($_POST['role'] ?? '')?>"></div>
    <div class="form-row"><label>Phone*</label><input type="text" name="phone" required value="<?=htmlspecialchars($_POST['phone'] ?? '')?>"></div>
    <div class="form-row"><label>Hire Date*</label><input type="date" name="hire_date" required value="<?=htmlspecialchars($_POST['hire_date'] ?? '')?>"></div>
    <div class="form-row"><label>Date of Birth*</label><input type="date" name="dob" required value="<?=htmlspecialchars($_POST['dob'] ?? '')?>"></div>
    <div class="form-row"><label>Salary*</label><input type="number" name="salary" step="0.01" required value="<?=htmlspecialchars($_POST['salary'] ?? '')?>"></div>
    <div class="form-row"><label>CNIC*</label><input type="text" name="cnic" required value="<?=htmlspecialchars($_POST['cnic'] ?? '')?>"></div>

    <div style="margin-top:12px"><button class="btn" type="submit">Save Employee</button> <a href="list.php" style="margin-left:10px;color:#6b7280">Cancel</a></div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>