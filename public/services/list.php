<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$rows = $pdo->query("SELECT * FROM service ORDER BY created_at DESC")->fetchAll();
$totalServices = count($rows);
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
  .service-table {
    margin-top:24px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(15,23,42,0.05);
  }
  .service-table table {
    width:100%;
    border-collapse:collapse;
  }
  .service-table thead {
    background:#f8fafc;
  }
  .service-table th,
  .service-table td {
    padding:12px 16px;
    text-align:left;
  }
  .service-table tbody tr {
    border-top:1px solid #f1f5f9;
  }
  .service-actions a {
    margin-right:10px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
  }
  .service-actions a.delete {
    color:#dc2626;
  }
</style>

<div class="service-hero">
  <div>
    <h2>Services</h2>
    <p><?= $totalServices ?> services configured</p>
  </div>
  <a class="btn" href="add.php">+ Add service</a>
</div>

<div class="service-table">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Service Name</th>
        <th>Base Cost</th>
        <th>Created At</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($totalServices === 0): ?>
        <tr>
          <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">
            No services yet.
          </td>
        </tr>
      <?php endif; ?>

      <?php foreach ($rows as $r): ?>
        <tr>
          <td>#<?= htmlspecialchars($r['id']) ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td>Rs <?= number_format((float)$r['base_cost'], 2) ?></td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td class="service-actions" style="text-align:right;">
            <a href="view.php?id=<?= $r['id'] ?>">View</a>
            <a href="edit.php?id=<?= $r['id'] ?>">Edit</a>
            <a
              class="delete"
              href="delete.php?id=<?= $r['id'] ?>"
              onclick="return confirm('Delete this service? This cannot be undone.');"
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
