<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

// MAIN SALE + CUSTOMER
$sql = "
  SELECT r.*, c.name AS customer_name, c.phone AS customer_phone
  FROM retailsale r
  JOIN customer c ON r.customer_id = c.id
  WHERE r.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$sale) {
  header('Location: list.php');
  exit;
}

// LINE ITEMS + PART INFO
// part table: id, name, brand_company, mrp,...
$detailSql = "
  SELECT d.*, p.name AS part_name, p.brand_company, p.mrp
  FROM retailsale_details d
  JOIN part p ON d.part_id = p.id
  WHERE d.retailsale_id = ?
";
$stmt = $pdo->prepare($detailSql);
$stmt->execute([$id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatCurrency($v) {
  return 'Rs ' . number_format((float)$v, 2);
}
function fmt($v, $fallback = 'Not provided') {
  if ($v === null) return $fallback;
  $t = trim((string)$v);
  return $t === '' ? $fallback : htmlspecialchars($t);
}
?>

<style>
  .retail-hero {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    padding:24px;
    border-radius:18px;
    background:linear-gradient(135deg,#ecfeff,#fef3c7);
    border:1px solid #bae6fd;
    gap:24px;
    flex-wrap:wrap;
    margin-bottom:24px;
  }
  .retail-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .retail-hero p { margin:6px 0 0;color:#475569; }
  .retail-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .retail-layout {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
  }
  .retail-card {
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
  .detail-table {
    margin-top:12px;
    border-radius:12px;
    border:1px solid #e2e8f0;
    overflow:hidden;
  }
  .detail-table table { width:100%;border-collapse:collapse; }
  .detail-table th,
  .detail-table td { padding:10px 14px;text-align:left; }
  .detail-table thead { background:#f9fafb; }
  .detail-table tbody tr { border-top:1px solid #e5e7eb; }
</style>

<div class="retail-hero">
  <div>
    <h2>Retail Sale #<?= htmlspecialchars($sale['id']) ?></h2>
    <p><?= fmt($sale['customer_name']) ?> · <?= htmlspecialchars($sale['sale_date']) ?></p>
  </div>
  <div>
    <a class="btn" href="list.php">Back to retail sales</a>
  </div>
</div>

<div class="retail-layout">
  <section class="retail-card">
    <h3>Sale Summary</h3>
    <div class="field-grid">
      <div class="field">
        <div class="label">Customer</div>
        <div class="value"><?= fmt($sale['customer_name']) ?></div>
      </div>
      <div class="field">
        <div class="label">Customer Phone</div>
        <div class="value"><?= fmt($sale['customer_phone']) ?></div>
      </div>
      <div class="field">
        <div class="label">Sale Date</div>
        <div class="value"><?= htmlspecialchars($sale['sale_date']) ?></div>
      </div>
      <div class="field">
        <div class="label">Total Amount</div>
        <div class="value"><?= formatCurrency($sale['total_amount']) ?></div>
      </div>
    </div>
  </section>

  <section class="retail-card">
    <h3>Line Items</h3>
    <div class="detail-table">
      <table>
        <thead>
          <tr>
            <th>Part</th>
            <th>Brand</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Line Total</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($details)): ?>
            <tr>
              <td colspan="5" style="text-align:center;color:#94a3b8;padding:12px;">
                No line items.
              </td>
            </tr>
          <?php endif; ?>

          <?php foreach ($details as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['part_name']) ?></td>
              <td><?= htmlspecialchars($d['brand_company']) ?></td>
              <td><?= htmlspecialchars($d['quantity']) ?></td>
              <td><?= formatCurrency($d['unit_price']) ?></td>
              <td><?= formatCurrency($d['line_total']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
