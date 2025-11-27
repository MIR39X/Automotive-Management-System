<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

$sql = "
  SELECT j.*,
         c.name      AS customer_name,
         c.phone     AS customer_phone,
         v.brand     AS vehicle_brand,
         v.model     AS vehicle_model,
         v.vin       AS vehicle_vin,
         e.name      AS employee_name
  FROM jobcard j
  JOIN customer c ON j.customer_id = c.id
  JOIN vehicle v  ON j.vehicle_id  = v.id
  JOIN employee e ON j.employee_id = e.id
  WHERE j.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  header('Location: list.php');
  exit;
}

function fmtVal($v, $fallback = 'Not provided') {
  if ($v === null) return $fallback;
  $t = trim((string)$v);
  return $t === '' ? $fallback : htmlspecialchars($t);
}
?>

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
  .jobcard-layout {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
    margin-top:8px;
  }
  .jobcard-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .field-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:12px;
    margin-top:10px;
  }
  .field {
    background:#f8fafc;
    border-radius:10px;
    padding:10px;
    border:1px solid #e2e8f0;
  }
  .field .label {
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:#94a3b8;
  }
  .field .value {
    margin-top:4px;
    font-size:15px;
    color:#0f172a;
  }
  .status-pill {
    display:inline-flex;
    padding:3px 10px;
    border-radius:999px;
    font-size:12px;
    text-transform:capitalize;
    font-weight:600;
  }
  .status-open { background:#fee2e2;color:#b91c1c; }
  .status-in_progress { background:#dbeafe;color:#1d4ed8; }
  .status-completed { background:#dcfce7;color:#15803d; }
  .status-closed { background:#e5e7eb;color:#374151; }
</style>

<div class="jobcard-hero">
  <div>
    <h2>Job Card #<?= htmlspecialchars($row['id']) ?></h2>
    <p><?= fmtVal($row['customer_name']) ?> · <?= fmtVal($row['vehicle_brand'] . ' ' . $row['vehicle_model']) ?></p>
  </div>
  <div>
    <a class="btn" href="edit.php?id=<?= $row['id'] ?>">Edit</a>
    <a class="btn" style="background:#fff;color:#2563eb;border:1px solid #2563eb;" href="list.php">Back to job cards</a>
  </div>
</div>

<div class="jobcard-layout">
  <section class="jobcard-card">
    <h3>Job Info</h3>
    <div class="field-grid">
      <div class="field">
        <div class="label">Status</div>
        <div class="value">
          <span class="status-pill status-<?= htmlspecialchars($row['status']) ?>">
            <?= htmlspecialchars(str_replace('_',' ',$row['status'])) ?>
          </span>
        </div>
      </div>
      <div class="field">
        <div class="label">Date In</div>
        <div class="value"><?= fmtVal($row['date_in']) ?></div>
      </div>
      <div class="field">
        <div class="label">Assigned Employee</div>
        <div class="value"><?= fmtVal($row['employee_name']) ?></div>
      </div>
    </div>

    <div class="field" style="margin-top:14px;">
      <div class="label">Notes</div>
      <div class="value"><?= nl2br(fmtVal($row['notes'], 'No notes added')) ?></div>
    </div>
  </section>

  <section class="jobcard-card">
    <h3>Customer & Vehicle</h3>
    <div class="field-grid">
      <div class="field">
        <div class="label">Customer</div>
        <div class="value"><?= fmtVal($row['customer_name']) ?></div>
      </div>
      <div class="field">
        <div class="label">Customer Phone</div>
        <div class="value"><?= fmtVal($row['customer_phone']) ?></div>
      </div>
      <div class="field">
        <div class="label">Vehicle</div>
        <div class="value"><?= fmtVal($row['vehicle_brand'] . ' ' . $row['vehicle_model']) ?></div>
      </div>
      <div class="field">
        <div class="label">VIN</div>
        <div class="value"><?= fmtVal($row['vehicle_vin'], 'N/A') ?></div>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
