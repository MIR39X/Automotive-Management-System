<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM part WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: list.php');
  exit;
}

function formatValue(?string $value, string $fallback = 'Not provided'): string {
  if ($value === null) return $fallback;
  $trimmed = trim($value);
  if ($trimmed === '') return $fallback;
  return htmlspecialchars($trimmed);
}

function formatCurrency($value, string $fallback = 'Rs 0.00'): string {
  if ($value === null || $value === '') return $fallback;
  return 'Rs ' . number_format((float)$value, 2);
}

$imagePath = null;
$hasImage = !empty($row['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $row['image']);
if ($hasImage) {
  $imagePath = '/ams_project/assets/uploads/' . $row['image']; // adjust if your base changes
}

// derive some display helpers
$partName   = $row['part_name'] ?? 'Unnamed part';
$company    = $row['company'] ?? '';
$compatible = $row['compatible_cars'] ?? '';
$qty        = $row['quantity'] ?? 0;
$warrantyOn = (int)($row['has_warranty'] ?? 0) === 1;
?>
<style>
  .part-hero {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    padding:24px;
    border-radius:18px;
    background:linear-gradient(135deg,#eff6ff,#ede9fe);
    border:1px solid #dbeafe;
    gap:24px;
    flex-wrap:wrap;
  }
  .part-eyebrow {
    text-transform:uppercase;
    letter-spacing:0.1em;
    font-size:12px;
    color:#6366f1;
    margin:0 0 6px;
  }
  .part-hero h2 {
    margin:0;
    font-size:30px;
    color:#0f172a;
  }
  .part-subtitle {
    margin-top:6px;
    color:#475569;
    font-size:14px;
  }
  .part-actions {
    display:flex;
    gap:10px;
    align-items:center;
  }
  .part-actions .btn {
    background-color:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .part-actions .btn.secondary {
    background:#fff;
    color:#2563eb;
    border:1px solid rgba(37,99,235,0.3);
  }
  .part-summary {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:16px;
  }
  .part-summary .card {
    border-radius:14px;
    border:1px solid #e2e8f0;
    padding:16px;
    background:#fff;
    box-shadow:0 8px 30px rgba(15,23,42,0.05);
  }
  .part-summary .label {
    font-size:12px;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:0.08em;
  }
  .part-summary .value {
    display:block;
    margin-top:6px;
    font-size:22px;
    font-weight:600;
    color:#0f172a;
  }
  .part-layout {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
  }
  .part-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .part-card h3 {
    margin-top:0;
    color:#0f172a;
  }
  .part-specs {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
    gap:14px;
    margin-top:20px;
  }
  .part-spec {
    background:#f8fafc;
    border-radius:12px;
    padding:12px;
    border:1px solid #e2e8f0;
  }
  .part-spec .label {
    font-size:12px;
    color:#94a3b8;
    text-transform:uppercase;
    letter-spacing:0.08em;
  }
  .part-spec .value {
    display:block;
    margin-top:6px;
    font-size:16px;
    color:#0f172a;
  }
  .part-image {
    width:100%;
    min-height:280px;
    border-radius:18px;
    border:1px solid #e2e8f0;
    background:#f1f5f9;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
  }
  .part-image img {
    width:100%;
    height:100%;
    object-fit:cover;
  }
  .warranty-pill {
    display:inline-flex;
    align-items:center;
    padding:4px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:600;
  }
  .warranty-yes {
    background:#dcfce7;
    color:#15803d;
  }
  .warranty-no {
    background:#fee2e2;
    color:#b91c1c;
  }
</style>

<div class="part-hero">
  <div>
    <p class="part-eyebrow">Part Profile</p>
    <h2><?= htmlspecialchars($partName) ?></h2>
    <p class="part-subtitle">
      <?= $company ? htmlspecialchars($company) : 'Company not set' ?>
      <?php if ($compatible): ?>
        &middot; Compatible: <?= htmlspecialchars($compatible) ?>
      <?php endif; ?>
    </p>
  </div>
  <div class="part-actions">
    <a class="btn" href="edit.php?id=<?= $row['id'] ?>">Edit part</a>
    <a class="btn secondary" href="list.php">Back to parts</a>
  </div>
</div>

<div class="part-summary">
  <div class="card">
    <span class="label">MRP</span>
    <span class="value"><?= formatCurrency($row['mrp']) ?></span>
  </div>
  <div class="card">
    <span class="label">Buying Price</span>
    <span class="value"><?= formatCurrency($row['buying_price']) ?></span>
  </div>
  <div class="card">
    <span class="label">Quantity</span>
    <span class="value"><?= (int)$qty ?></span>
  </div>
  <div class="card">
    <span class="label">Warranty</span>
    <span class="value">
      <?php if ($warrantyOn): ?>
        <span class="warranty-pill warranty-yes">Yes</span>
      <?php else: ?>
        <span class="warranty-pill warranty-no">No</span>
      <?php endif; ?>
    </span>
  </div>
</div>

<div class="part-layout">
  <section class="part-card">
    <h3>Gallery</h3>
    <div class="part-image">
      <?php if ($imagePath): ?>
        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($partName) ?>">
      <?php else: ?>
        <div style="color:#94a3b8;font-size:14px">No image available</div>
      <?php endif; ?>
    </div>
  </section>

  <section class="part-card">
    <h3>Details</h3>
    <div class="part-specs">
      <div class="part-spec">
        <span class="label">Part Name</span>
        <span class="value"><?= formatValue($row['part_name'] ?? '', 'N/A') ?></span>
      </div>
      <div class="part-spec">
        <span class="label">Company</span>
        <span class="value"><?= formatValue($row['company'] ?? '', 'N/A') ?></span>
      </div>
      <div class="part-spec">
        <span class="label">Compatible Makes</span>
        <span class="value"><?= formatValue($row['compatible_cars'] ?? '', 'Not set') ?></span>
      </div>
      <div class="part-spec">
        <span class="label">Supplier</span>
        <span class="value"><?= formatValue($row['supplier_name'] ?? '', 'Not set') ?></span>
      </div>
      <div class="part-spec">
        <span class="label">Quantity</span>
        <span class="value"><?= (int)$qty ?></span>
      </div>
      <div class="part-spec">
        <span class="label">Has Warranty</span>
        <span class="value"><?= $warrantyOn ? 'Yes' : 'No' ?></span>
      </div>
      <div class="part-spec">
        <span class="label">Buying Price</span>
        <span class="value"><?= formatCurrency($row['buying_price']) ?></span>
      </div>
      <div class="part-spec">
        <span class="label">MRP</span>
        <span class="value"><?= formatCurrency($row['mrp']) ?></span>
      </div>
    </div>
  </section>
</div>

<section class="part-card" style="margin-top:24px;">
  <h3>Description</h3>
  <p style="color:#475569;line-height:1.6">
    <?= nl2br(formatValue($row['description'] ?? '', 'No description provided')) ?>
  </p>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
