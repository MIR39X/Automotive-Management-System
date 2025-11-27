<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$rows = $pdo->query("SELECT * FROM supplier ORDER BY created_at DESC")->fetchAll();
$totalSuppliers = count($rows);
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
  .supplier-table {
    margin-top:24px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(15,23,42,0.05);
  }
  .supplier-table table {
    width:100%;
    border-collapse:collapse;
  }
  .supplier-table thead {
    background:#f8fafc;
  }
  .supplier-table th,
  .supplier-table td {
    padding:12px 16px;
    text-align:left;
  }
  .supplier-table tbody tr {
    border-top:1px solid #f1f5f9;
  }
  .supplier-actions a {
    margin-right:10px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
  }
  .supplier-actions a.delete {
    color:#dc2626;
  }
</style>

<div class="supplier-hero">
  <div>
    <h2>Suppliers</h2>
    <p><?= $totalSuppliers ?> suppliers in system</p>
  </div>
  <a class="btn" href="add.php">+ Add supplier</a>
</div>

<div class="supplier-table">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Supplier</th>
        <th>Phone</th>
        <th>Email</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($totalSuppliers === 0): ?>
        <tr>
          <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">
            No suppliers yet.
          </td>
        </tr>
      <?php endif; ?>

      <?php foreach ($rows as $r): ?>
        <tr>
          <td>#<?= htmlspecialchars($r['id']) ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['phone']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td class="supplier-actions" style="text-align:right;">
            <a href="view.php?id=<?= $r['id'] ?>">View</a>
            <a href="edit.php?id=<?= $r['id'] ?>">Edit</a>
            <a
              class="delete"
              href="delete.php?id=<?= $r['id'] ?>"
              onclick="return confirm('Delete this supplier? This cannot be undone.');"
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
