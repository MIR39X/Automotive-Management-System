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

$stmt = $pdo->prepare("SELECT * FROM service WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: list.php');
  exit;
}

$errors = [];
$name        = $row['name'];
$base_cost   = $row['base_cost'];
$description = $row['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name        = trim($_POST['name'] ?? '');
  $base_cost   = trim($_POST['base_cost'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if ($name === '') {
    $errors[] = 'Service name is required';
  }
  if ($base_cost !== '' && !is_numeric($base_cost)) {
    $errors[] = 'Base cost must be a number';
  }

  if (empty($errors)) {
    $costValue = ($base_cost === '') ? 0 : (float)$base_cost;
    $stmt = $pdo->prepare("UPDATE service SET name = ?, base_cost = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $costValue, $description, $id]);
    header('Location: list.php');
    exit;
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
  .service-hero {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    padding:24px;
    border-radius:18px;
    background:linear-gradient(135deg,#eff6ff,#e0f2fe);
    border:1px solid #dbeafe;
    gap:24px;
    flex-wrap:wrap;
    margin-bottom:24px;
  }
  .service-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .service-hero p { margin:6px 0 0;color:#475569; }
  .service-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .service-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .form-group { margin-bottom:14px;display:flex;flex-direction:column; }
  .form-group label {
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:#94a3b8;
    margin-bottom:6px;
  }
  .form-group input,
  .form-group textarea {
    padding:12px;
    border-radius:10px;
    border:1px solid #e2e8f0;
    font-size:15px;
    color:#0f172a;
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

<div class="service-hero">
  <div>
    <h2>Edit Service</h2>
    <p>Update service details and base cost.</p>
  </div>
  <a class="btn" href="list.php">Back to services</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?>
      <?= htmlspecialchars($e) . '<br>' ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="service-card">
  <form method="post" novalidate>
    <div class="form-group">
      <label>Service Name*</label>
      <input type="text" name="name" required value="<?= htmlspecialchars($name) ?>">
    </div>
    <div class="form-group">
      <label>Base Cost</label>
      <input type="number" step="0.01" name="base_cost" value="<?= htmlspecialchars($base_cost) ?>">
    </div>
    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Update service</button>
      <a href="list.php">Cancel</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
