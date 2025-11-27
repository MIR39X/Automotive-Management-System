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

$stmt = $pdo->prepare("SELECT * FROM supplier WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: list.php');
  exit;
}

$errors = [];
$name    = $row['name'];
$phone   = $row['phone'];
$email   = $row['email'];
$address = $row['address'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name    = trim($_POST['name'] ?? '');
  $phone   = trim($_POST['phone'] ?? '');
  $email   = trim($_POST['email'] ?? '');
  $address = trim($_POST['address'] ?? '');

  if ($name === '') {
    $errors[] = 'Name is required';
  }

  if (empty($errors)) {
    $stmt = $pdo->prepare("UPDATE supplier SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $email, $address, $id]);
    header('Location: list.php');
    exit;
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
  .supplier-hero {
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
  .supplier-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .supplier-hero p { margin:6px 0 0;color:#475569; }
  .supplier-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .supplier-card {
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

<div class="supplier-hero">
  <div>
    <h2>Edit Supplier</h2>
    <p>Update supplier contact and address information.</p>
  </div>
  <a class="btn" href="list.php">Back to suppliers</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?>
      <?= htmlspecialchars($e) . '<br>' ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="supplier-card">
  <form method="post" novalidate>
    <div class="form-group">
      <label>Name*</label>
      <input type="text" name="name" required value="<?= htmlspecialchars($name) ?>">
    </div>
    <div class="form-group">
      <label>Phone</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
    </div>
    <div class="form-group">
      <label>Address</label>
      <textarea name="address" rows="3"><?= htmlspecialchars($address) ?></textarea>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Update supplier</button>
      <a href="list.php">Cancel</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
