<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

// parts + supplier name
$sql = "
  SELECT p.*, s.name AS supplier_name
  FROM part p
  LEFT JOIN supplier s ON p.supplier_id = s.id
  ORDER BY p.created_at DESC, p.id DESC
";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$totalParts = count($rows);

function formatCurrency($v) {
  return 'Rs ' . number_format((float)$v, 2);
}
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
  .part-hero h2 {
    margin:0;
    font-size:30px;
    color:#0f172a;
  }
  .part-hero p {
    margin:6px 0 0;
    color:#475569;
  }
  .part-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .part-summary {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
  }
  .part-summary .card {
    border:1px solid #e2e8f0;
    border-radius:12px;
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

  .part-table {
    margin-top:24px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(15,23,42,0.05);
  }
  .part-table table {
    width:100%;
    border-collapse:collapse;
  }
  .part-table thead {
    background:#f8fafc;
  }
  .part-table th,
  .part-table td {
    padding:12px 16px;
    text-align:left;
  }
  .part-table tbody tr {
    border-top:1px solid #f1f5f9;
  }

  .part-thumb {
    width:70px;
    height:50px;
    border-radius:10px;
    border:1px solid #e2e8f0;
    background:#f8fafc;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    font-size:12px;
    color:#94a3b8;
  }
  .part-thumb img {
    width:100%;
    height:100%;
    object-fit:cover;
  }

  .part-actions a {
    margin-right:10px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
  }
  .part-actions a.delete {
    color:#dc2626;
  }
  .badge {
    display:inline-flex;
    align-items:center;
    padding:2px 10px;
    border-radius:999px;
    font-size:12px;
    background:#e5e7eb;
    color:#374151;
  }
</style>

<div class="part-hero">
  <div>
    <h2>Parts Inventory</h2>
    <p><?= $totalParts ?> parts in catalog</p>
  </div>
  <a class="btn" href="add.php">+ Add part</a>
</div>

<div class="part-summary">
  <div class="card">
    <span class="label">Total Parts</span>
    <span class="value"><?= $totalParts ?></span>
  </div>
  <div class="card">
    <span class="label">Total Quantity</span>
    <span class="value">
      <?php
        $totalQty = array_sum(array_map(fn($r) => (int)($r['quantity'] ?? 0), $rows));
        echo $totalQty;
      ?>
    </span>
  </div>
  <div class="card">
    <span class="label">Total MRP Value</span>
    <span class="value">
      <?php
        $totalValue = array_sum(array_map(fn($r) => (float)($r['mrp'] ?? 0) * (int)($r['quantity'] ?? 0), $rows));
        echo formatCurrency($totalValue);
      ?>
    </span>
  </div>
</div>

<div class="part-table">
  <table role="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Part</th>
        <th>Brand / Company</th>
        <th>Supplier</th>
        <th>Qty</th>
        <th>MRP</th>
        <th style="text-align:right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($totalParts === 0): ?>
        <tr>
          <td colspan="7" style="text-align:center;color:#94a3b8;padding:20px;">
            No parts added yet.
          </td>
        </tr>
      <?php endif; ?>

      <?php foreach ($rows as $r): ?>
        <?php
          // yahan sahi label banayenge:
          $partLabel = trim(($r['name'] ?? ''));
          if ($partLabel === '') {
            $partLabel = 'Unnamed part';
          }
        ?>
        <tr>
          <td>#<?= htmlspecialchars($r['id']) ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:12px;">
              <div class="part-thumb">
                <?php if (!empty($r['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $r['image'])): ?>
                  <img src="/ams_project/assets/uploads/<?= htmlspecialchars($r['image']) ?>" alt="">
                <?php else: ?>
                  No Image
                <?php endif; ?>
              </div>
              <div>
                <div style="font-weight:600;color:#0f172a">
                  <?= htmlspecialchars($partLabel) ?>
                </div>
                <?php if (!empty($r['compatible_makes'])): ?>
                  <div style="font-size:12px;color:#6b7280;">
                    For: <?= htmlspecialchars($r['compatible_makes']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($r['brand_company'] ?? '-') ?></td>
          <td>
            <?php if (!empty($r['supplier_name'])): ?>
              <span class="badge"><?= htmlspecialchars($r['supplier_name']) ?></span>
            <?php else: ?>
              <span style="font-size:12px;color:#9ca3af;">No supplier</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($r['quantity']) ?></td>
          <td><?= formatCurrency($r['mrp']) ?></td>
          <td class="part-actions" style="text-align:right;">
            <a href="view.php?id=<?= $r['id'] ?>">View</a>
            <a href="edit.php?id=<?= $r['id'] ?>">Edit</a>
            <a
              class="delete"
              href="delete.php?id=<?= $r['id'] ?>"
              onclick="return confirm('Delete this part? This action cannot be undone.');"
            >
              Delete
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
