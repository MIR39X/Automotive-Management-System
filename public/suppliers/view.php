<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

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

function fmt($value, $fallback = 'Not provided') {
  if ($value === null) return $fallback;
  $t = trim((string)$value);
  return $t === '' ? $fallback : htmlspecialchars($t);
}
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
  .supplier-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
    margin-top:16px;
  }
  .supplier-field {
    background:#f8fafc;
    border-radius:10px;
    padding:12px;
    border:1px solid #e2e8f0;
  }
  .supplier-field .label {
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:#94a3b8;
  }
  .supplier-field .value {
    margin-top:4px;
    font-size:15px;
    color:#0f172a;
  }
</style>

<div class="supplier-hero">
  <div>
    <h2><?= fmt($row['name'], 'Supplier') ?></h2>
    <p>Full profile of this supplier.</p>
  </div>
  <div>
    <a class="btn" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
    <a class="btn" style="background:#fff;color:#2563eb;border:1px solid #2563eb;" href="list.php">Back to suppliers</a>
  </div>
</div>

<section class="supplier-card">
  <h3>Contact Details</h3>
  <div class="supplier-grid">
    <div class="supplier-field">
      <div class="label">Name</div>
      <div class="value"><?= fmt($row['name']) ?></div>
    </div>
    <div class="supplier-field">
      <div class="label">Phone</div>
      <div class="value"><?= fmt($row['phone']) ?></div>
    </div>
    <div class="supplier-field">
      <div class="label">Email</div>
      <div class="value"><?= fmt($row['email']) ?></div>
    </div>
    <div class="supplier-field">
      <div class="label">Address</div>
      <div class="value"><?= nl2br(fmt($row['address'])) ?></div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
