<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

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

function fmt($value, $fallback = 'Not provided') {
  if ($value === null) return $fallback;
  $t = trim((string)$value);
  return $t === '' ? $fallback : htmlspecialchars($t);
}
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
  .service-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
    margin-top:16px;
  }
  .service-field {
    background:#f8fafc;
    border-radius:10px;
    padding:12px;
    border:1px solid #e2e8f0;
  }
  .service-field .label {
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:#94a3b8;
  }
  .service-field .value {
    margin-top:4px;
    font-size:15px;
    color:#0f172a;
  }
</style>

<div class="service-hero">
  <div>
    <h2><?= fmt($row['name'], 'Service') ?></h2>
    <p>Service definition used in job cards and billing.</p>
  </div>
  <div>
    <a class="btn" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
    <a class="btn" style="background:#fff;color:#2563eb;border:1px solid #2563eb;" href="list.php">Back to services</a>
  </div>
</div>

<section class="service-card">
  <h3>Details</h3>
  <div class="service-grid">
    <div class="service-field">
      <div class="label">Name</div>
      <div class="value"><?= fmt($row['name']) ?></div>
    </div>
    <div class="service-field">
      <div class="label">Base Cost</div>
      <div class="value">Rs <?= number_format((float)$row['base_cost'], 2) ?></div>
    </div>
    <div class="service-field" style="grid-column:1/-1;">
      <div class="label">Description</div>
      <div class="value"><?= nl2br(fmt($row['description'])) ?></div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
