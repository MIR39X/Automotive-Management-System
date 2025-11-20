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

$errors = [];
$form = [
  'name' => '',
  'role' => '',
  'phone' => '',
  'hire_date' => '',
  'dob' => '',
  'salary' => '',
  'cnic' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form['name'] = trim($_POST['name'] ?? '');
  $form['role'] = trim($_POST['role'] ?? '');
  $form['phone'] = trim($_POST['phone'] ?? '');
  $form['hire_date'] = $_POST['hire_date'] ?? null;
  $form['dob'] = $_POST['dob'] ?? null;
  $form['salary'] = $_POST['salary'] ?? '';
  $form['cnic'] = trim($_POST['cnic'] ?? '');

  $salaryValue = floatval($form['salary'] !== '' ? $form['salary'] : 0);

  if ($form['name'] === '') $errors[] = 'Name is required';
  if ($form['role'] === '') $errors[] = 'Role is required';
  if ($form['phone'] === '') $errors[] = 'Phone is required';
  if ($form['hire_date'] === null || $form['hire_date'] === '') $errors[] = 'Hire date is required';
  if ($form['dob'] === null || $form['dob'] === '') $errors[] = 'Date of birth is required';
  if ($salaryValue <= 0) $errors[] = 'Salary must be greater than 0';
  if ($form['cnic'] === '') $errors[] = 'CNIC is required';

  if (empty($errors)) {
    $stmt = $pdo->prepare("INSERT INTO employee (name, role, phone, hire_date, dob, salary, cnic) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$form['name'], $form['role'], $form['phone'], $form['hire_date'], $form['dob'], $salaryValue, $form['cnic']]);
    header('Location: list.php');
    exit;
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
  .employee-hero {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    padding:24px;
    border-radius:18px;
    background:linear-gradient(135deg,#eff6ff,#ede9fe);
    border:1px solid #dbeafe;
    gap:24px;
    flex-wrap:wrap;
    margin-bottom:24px;
  }
  .employee-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .employee-hero p { margin:6px 0 0;color:#475569; }
  .employee-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .employee-form-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
  }
  .employee-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .employee-card h3 { margin-top:0;color:#0f172a; }
  .form-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
  }
  .form-group { display:flex;flex-direction:column; }
  .form-group label {
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:#94a3b8;
    margin-bottom:6px;
  }
  .form-group input,
  .form-group textarea,
  .form-group select {
    padding:12px;
    border-radius:10px;
    border:1px solid #e2e8f0;
    font-size:15px;
    color:#0f172a;
  }
  .form-actions {
    margin-top:24px;
    display:flex;
    gap:12px;
    flex-wrap:wrap;
  }
  .form-actions .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 22px;
    border-radius:5px;
    border:none;
    cursor:pointer;
  }
  .form-actions a {
    padding:10px 22px;
    border-radius:5px;
    border:1px solid rgba(37,99,235,0.3);
    color:#2563eb;
    text-decoration:none;
  }
  .error-box {
    color:#b91c1c;
    margin-bottom:14px;
    padding:12px;
    border:1px solid #fecdd3;
    border-radius:10px;
    background:#fff1f2;
  }
</style>

<div class="employee-hero">
  <div>
    <h2>Add Employee</h2>
    <p>Register a new team member with personal and employment details.</p>
  </div>
  <a class="btn" href="list.php">Back to employees</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?><?=htmlspecialchars($e) . '<br>'?><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" novalidate>
  <div class="employee-form-grid">
    <section class="employee-card">
      <h3>Personal Information</h3>
      <div class="form-grid">
        <div class="form-group">
          <label>Name*</label>
          <input type="text" name="name" required value="<?=htmlspecialchars($form['name'])?>">
        </div>
        <div class="form-group">
          <label>Date of Birth*</label>
          <input type="date" name="dob" required value="<?=htmlspecialchars($form['dob'])?>">
        </div>
        <div class="form-group">
          <label>CNIC*</label>
          <input type="text" name="cnic" required value="<?=htmlspecialchars($form['cnic'])?>">
        </div>
        <div class="form-group">
          <label>Phone*</label>
          <input type="text" name="phone" required value="<?=htmlspecialchars($form['phone'])?>">
        </div>
      </div>
    </section>

    <section class="employee-card">
      <h3>Employment Details</h3>
      <div class="form-grid">
        <div class="form-group">
          <label>Role*</label>
          <input type="text" name="role" required value="<?=htmlspecialchars($form['role'])?>">
        </div>
        <div class="form-group">
          <label>Hire Date*</label>
          <input type="date" name="hire_date" required value="<?=htmlspecialchars($form['hire_date'])?>">
        </div>
        <div class="form-group">
          <label>Salary*</label>
          <input type="number" name="salary" step="0.01" required value="<?=htmlspecialchars($form['salary'])?>">
        </div>
      </div>
    </section>
  </div>

  <div class="form-actions">
    <button class="btn" type="submit">Save Employee</button>
    <a href="list.php">Cancel</a>
  </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>





