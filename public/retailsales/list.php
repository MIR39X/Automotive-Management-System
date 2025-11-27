<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$sql = "
  SELECT r.*, c.name AS customer_name
  FROM retailsale r
  JOIN customer c ON r.customer_id = c.id
  ORDER BY r.sale_date DESC, r.id DESC
";
$rows = $pdo->query($sql)->fetchAll();
$totalSales = count($rows);

function formatCurrency($v) {
  return 'Rs ' . number_format((float)$v, 2);
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
  .retail-table {
    margin-top:24px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(15,23,42,0.05);
  }
  .retail-table table { width:100%;border-collapse:collapse; }
  .retail-table thead { background:#f8fafc; }
  .retail-table th,
  .retail-table td { padding:12px 16px;text-align:left; }
  .retail-table tbody tr { border-top:1px solid #f1f5f9; }
  .retail-actions a {
    margin-right:10px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
  }
  .retail-actions a.delete { color:#dc2626; }
</style>

<div class="retail-hero">
  <div>
    <h2>Retail Sales (Parts)</h2>
    <p><?= $totalSales ?> sales recorded</p>
  </div>
  <a class="btn" href="add.php">+ New retail sale</a>
</div>

<div class="retail-table">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Customer</th>
        <th>Sale Date</th>
        <th>Total Amount</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($totalSales === 0): ?>
        <tr>
          <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">
            No retail sales yet.
          </td>
        </tr>
      <?php endif; ?>

      <?php foreach ($rows as $r): ?>
        <tr>
          <td>#<?= htmlspecialchars($r['id']) ?></td>
          <td><?= htmlspecialchars($r['customer_name']) ?></td>
          <td><?= htmlspecialchars($r['sale_date']) ?></td>
          <td><?= formatCurrency($r['total_amount']) ?></td>
          <td class="retail-actions" style="text-align:right;">
            <a href="view.php?id=<?= $r['id'] ?>">View</a>
            <a
              class="delete"
              href="delete.php?id=<?= $r['id'] ?>"
              onclick="return confirm('Delete this retail sale and its line item?');"
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
