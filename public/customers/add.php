<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $notes = trim($_POST['notes'] ?? '');

  if ($name === '') $errors[] = 'Name is required';
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email';

  if (empty($errors)) {
    $stmt = $pdo->prepare("INSERT INTO customer (name, phone, email, address, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $email, $address, $notes]);
    header('Location: list.php');
    exit;
  }
}
?>
<div class="page-hero" style="display:flex;align-items:center;justify-content:space-between">
  <h2>Add customer</h2>
  <a class="btn" href="list.php">Back to customers</a>
</div>

<div class="card">
  <?php if (!empty($errors)): ?>
    <div style="color:#b91c1c;margin-bottom:10px"><?php foreach($errors as $e) echo htmlspecialchars($e).'<br>' ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="form-row"><label>Name*</label><input type="text" name="name" required value="<?=htmlspecialchars($_POST['name'] ?? '')?>"></div>
    <div class="form-row"><label>Phone</label><input type="text" name="phone" value="<?=htmlspecialchars($_POST['phone'] ?? '')?>"></div>
    <div class="form-row"><label>Email</label><input type="text" name="email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>"></div>
    <div class="form-row"><label>Address</label><textarea name="address"><?=htmlspecialchars($_POST['address'] ?? '')?></textarea></div>
    <div class="form-row"><label>Notes</label><textarea name="notes"><?=htmlspecialchars($_POST['notes'] ?? '')?></textarea></div>

    <div style="margin-top:12px"><button class="btn" type="submit">Save customer</button> <a href="list.php" style="margin-left:10px;color:#6b7280">Cancel</a></div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
