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
if (!$id) {
  header('Location: list.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM jobcard WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: list.php');
  exit;
}

// dropdown data
$customers = $pdo->query("SELECT id, name FROM customer ORDER BY name")->fetchAll();
$vehicles  = $pdo->query("SELECT id, brand, model FROM vehicle ORDER BY brand, model")->fetchAll();
$employees = $pdo->query("SELECT id, name FROM employee ORDER BY name")->fetchAll();

$errors = [];
$customer_id = $row['customer_id'];
$vehicle_id  = $row['vehicle_id'];
$employee_id = $row['employee_id'];
$date_in     = $row['date_in'];
$status      = $row['status'];
$notes       = $row['notes'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $customer_id = intval($_POST['customer_id'] ?? 0);
  $vehicle_id  = intval($_POST['vehicle_id'] ?? 0);
  $employee_id = intval($_POST['employee_id'] ?? 0);
  $date_in     = $_POST['date_in'] ?? '';
  $status      = $_POST['status'] ?? 'open';
  $notes       = trim($_POST['notes'] ?? '');

  if ($customer_id <= 0) $errors[] = 'Customer is required';
  if ($vehicle_id  <= 0) $errors[] = 'Vehicle is required';
  if ($employee_id <= 0) $errors[] = 'Assigned employee is required';
  if ($date_in === '')   $errors[] = 'Date in is required';

  if (empty($errors)) {
    $stmt = $pdo->prepare("
      UPDATE jobcard
      SET customer_id = ?, vehicle_id = ?, employee_id = ?, date_in = ?, status = ?, notes = ?
      WHERE id = ?
    ");
    $stmt->execute([$customer_id, $vehicle_id, $employee_id, $date_in, $status, $notes, $id]);
    header('Location: list.php');
    exit;
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- CSS same as add.php, you can keep or reuse -->

<style>
  .jobcard-hero {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    padding:24px;
    border-radius:18px;
    background:linear-gradient(135deg,#eff6ff,#fef3c7);
    border:1px solid #fde68a;
    gap:24px;
    flex-wrap:wrap;
    margin-bottom:24px;
  }
  .jobcard-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .jobcard-hero p { margin:6px 0 0;color:#475569; }
  .jobcard-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .jobcard-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
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
  .form-group select,
  .form-group input,
  .form-group textarea {
    padding:10px;
    border-radius:10px;
    border:1px solid #e2e8f0;
    font-size:15px;
    color:#0f172a;
  }
  .form-group textarea {
    min-height:100px;
    resize:vertical;
  }
  .form-actions {
    margin-top:20px;
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

<div class="jobcard-hero">
  <div>
    <h2>Edit Job Card</h2>
    <p>Update assignment, status, or notes for this job card.</p>
  </div>
  <a class="btn" href="list.php">Back to job cards</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?>
      <?= htmlspecialchars($e) . '<br>' ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="jobcard-card">
  <form method="post" novalidate>
    <div class="form-grid">
      <div class="form-group">
        <label>Customer*</label>
        <select name="customer_id" required>
          <option value="">-- Select customer --</option>
          <?php foreach ($customers as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $customer_id == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Vehicle*</label>
        <select name="vehicle_id" required>
          <option value="">-- Select vehicle --</option>
          <?php foreach ($vehicles as $v): ?>
            <option value="<?= $v['id'] ?>" <?= $vehicle_id == $v['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($v['brand'] . ' ' . $v['model']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Assigned Employee*</label>
        <select name="employee_id" required>
          <option value="">-- Select employee --</option>
          <?php foreach ($employees as $e): ?>
            <option value="<?= $e['id'] ?>" <?= $employee_id == $e['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($e['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Date In*</label>
        <input type="date" name="date_in" value="<?= htmlspecialchars($date_in) ?>" required>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select name="status">
          <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>open</option>
          <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>in progress</option>
          <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>completed</option>
          <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>closed</option>
        </select>
      </div>
    </div>

    <div class="form-group" style="margin-top:16px;">
      <label>Notes</label>
      <textarea name="notes"><?= htmlspecialchars($notes) ?></textarea>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Update job card</button>
      <a href="list.php">Cancel</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
