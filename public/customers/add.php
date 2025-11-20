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
  'phone' => '',
  'email' => '',
  'address' => '',
  'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form['name'] = trim($_POST['name'] ?? '');
  $form['phone'] = trim($_POST['phone'] ?? '');
  $form['email'] = trim($_POST['email'] ?? '');
  $form['address'] = trim($_POST['address'] ?? '');
  $form['notes'] = trim($_POST['notes'] ?? '');

  if ($form['name'] === '') $errors[] = 'Name is required';
  if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';

  if (empty($errors)) {
    $stmt = $pdo->prepare("INSERT INTO customer (name, phone, email, address, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$form['name'], $form['phone'], $form['email'], $form['address'], $form['notes']]);
    header('Location: list.php');
    exit;
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<style>
  .customer-hero {
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
  .customer-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .customer-hero p { margin:6px 0 0;color:#475569; }
  .customer-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .customer-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
    margin-bottom:20px;
  }
  .customer-card h3 { margin-top:0;color:#0f172a; }
  .form-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
  }
  .form-group {
    display:flex;
    flex-direction:column;
  }
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
  .form-group textarea {
    min-height:120px;
    resize:vertical;
  }
  .form-actions {
    margin-top:12px;
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

<div class="customer-hero">
  <div>
    <h2>Add Customer</h2>
    <p>Capture a new customer with contact and CRM notes.</p>
  </div>
  <a class="btn" href="list.php">Back to customers</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?><?=htmlspecialchars($e) . '<br>'?><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" novalidate>
  <section class="customer-card">
    <h3>Contact Details</h3>
    <div class="form-grid">
      <div class="form-group">
        <label>Name*</label>
        <input type="text" name="name" required value="<?=htmlspecialchars($form['name'])?>">
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" value="<?=htmlspecialchars($form['phone'])?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="text" name="email" value="<?=htmlspecialchars($form['email'])?>">
      </div>
    </div>
  </section>

  <section class="customer-card">
    <h3>Address & Notes</h3>
    <div class="form-group" style="margin-bottom:16px;">
      <label>Address</label>
      <textarea name="address"><?=htmlspecialchars($form['address'])?></textarea>
    </div>
    <div class="form-group">
      <label>Notes</label>
      <textarea name="notes"><?=htmlspecialchars($form['notes'])?></textarea>
    </div>
  </section>

  <div class="form-actions">
    <button class="btn" type="submit">Save customer</button>
    <a href="list.php">Cancel</a>
  </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
