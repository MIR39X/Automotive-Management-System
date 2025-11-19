<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM vehicle WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
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

function formatCurrency($value, string $fallback = '$0.00'): string {
  if ($value === null || $value === '') return $fallback;
  return '$' . number_format((float)$value, 2);
}

$imagePath = null;
$hasImage = !empty($row['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $row['image']);
if ($hasImage) {
  $imagePath = '/ams_project/assets/uploads/' . $row['image'];
}
?>

<style>
  .vehicle-hero {
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
  .vehicle-eyebrow {
    text-transform:uppercase;
    letter-spacing:0.1em;
    font-size:12px;
    color:#6366f1;
    margin:0 0 6px;
  }
  .vehicle-hero h2 {
    margin:0;
    font-size:30px;
    color:#0f172a;
  }
  .vehicle-subtitle {
    margin-top:6px;
    color:#475569;
    font-size:14px;
  }
  .vehicle-actions {
    display:flex;
    gap:10px;
    align-items:center;
  }
  .vehicle-actions .btn {
    background-color:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .vehicle-actions .btn.secondary {
    background:#fff;
    color:#2563eb;
    border:1px solid rgba(37,99,235,0.3);
  }
  .vehicle-summary {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:16px;
  }
  .vehicle-summary .card {
    border-radius:14px;
    border:1px solid #e2e8f0;
    padding:16px;
    background:#fff;
    box-shadow:0 8px 30px rgba(15,23,42,0.05);
  }
  .vehicle-summary .label {
    font-size:12px;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:0.08em;
  }
  .vehicle-summary .value {
    display:block;
    margin-top:6px;
    font-size:22px;
    font-weight:600;
    color:#0f172a;
  }
  .vehicle-layout {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
  }
  .vehicle-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .vehicle-card h3 {
    margin-top:0;
    color:#0f172a;
  }
  .vehicle-specs {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
    gap:14px;
    margin-top:20px;
  }
  .vehicle-spec {
    background:#f8fafc;
    border-radius:12px;
    padding:12px;
    border:1px solid #e2e8f0;
  }
  .vehicle-spec .label {
    font-size:12px;
    color:#94a3b8;
    text-transform:uppercase;
    letter-spacing:0.08em;
  }
  .vehicle-spec .value {
    display:block;
    margin-top:6px;
    font-size:16px;
    color:#0f172a;
  }
  .vehicle-image {
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
  .vehicle-image img {
    width:100%;
    height:100%;
    object-fit:cover;
  }
  .status-pill {
    display:inline-flex;
    align-items:center;
    padding:4px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:600;
    text-transform:capitalize;
  }
  .status-available {
    background:#dcfce7;
    color:#15803d;
  }
  .status-sold {
    background:#fee2e2;
    color:#b91c1c;
  }
  .status-service {
    background:#fef3c7;
    color:#92400e;
  }
</style>

<div class="vehicle-hero">
  <div>
    <p class="vehicle-eyebrow">Vehicle Profile</p>
    <h2><?=htmlspecialchars($row['brand'].' '.$row['model'])?></h2>
    <p class="vehicle-subtitle">
      VIN <?=formatValue($row['vin'], 'N/A')?> &middot;
      <?=formatValue($row['year'] ? (string)$row['year'] : null, 'Year not set')?>
    </p>
  </div>
  <div class="vehicle-actions">
    <a class="btn" href="edit.php?id=<?=$row['id']?>">Edit vehicle</a>
    <a class="btn secondary" href="list.php">Back to vehicles</a>
  </div>
</div>

<div class="vehicle-summary">
  <div class="card">
    <span class="label">Price</span>
    <span class="value"><?=formatCurrency($row['price'])?></span>
  </div>
  <div class="card">
    <span class="label">Status</span>
    <span class="value status-pill status-<?=htmlspecialchars($row['status'])?>"><?=htmlspecialchars($row['status'])?></span>
  </div>
  <div class="card">
    <span class="label">VIN</span>
    <span class="value"><?=formatValue($row['vin'], 'N/A')?></span>
  </div>
  <div class="card">
    <span class="label">Model Year</span>
    <span class="value"><?=formatValue($row['year'] ? (string)$row['year'] : null, 'Not set')?></span>
  </div>
</div>

<div class="vehicle-layout">
  <section class="vehicle-card">
    <h3>Gallery</h3>
    <div class="vehicle-image">
      <?php if ($imagePath): ?>
        <img src="<?=$imagePath?>" alt="<?=htmlspecialchars($row['brand'].' '.$row['model'])?>">
      <?php else: ?>
        <div style="color:#94a3b8;font-size:14px">No image available</div>
      <?php endif; ?>
    </div>
  </section>

  <section class="vehicle-card">
    <h3>Specifications</h3>
    <div class="vehicle-specs">
      <div class="vehicle-spec">
        <span class="label">Brand</span>
        <span class="value"><?=formatValue($row['brand'], 'N/A')?></span>
      </div>
      <div class="vehicle-spec">
        <span class="label">Model</span>
        <span class="value"><?=formatValue($row['model'], 'N/A')?></span>
      </div>
      <div class="vehicle-spec">
        <span class="label">VIN</span>
        <span class="value"><?=formatValue($row['vin'], 'N/A')?></span>
      </div>
      <div class="vehicle-spec">
        <span class="label">Year</span>
        <span class="value"><?=formatValue($row['year'] ? (string)$row['year'] : null, 'Not set')?></span>
      </div>
      <div class="vehicle-spec">
        <span class="label">Status</span>
        <span class="value"><?=htmlspecialchars($row['status'])?></span>
      </div>
      <div class="vehicle-spec">
        <span class="label">Price</span>
        <span class="value"><?=formatCurrency($row['price'])?></span>
      </div>
    </div>
  </section>
</div>

<section class="vehicle-card" style="margin-top:24px;">
  <h3>Description</h3>
  <p style="color:#475569;line-height:1.6">
    <?=nl2br(formatValue($row['description'] ?? '', 'No description provided'))?>
  </p>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>






