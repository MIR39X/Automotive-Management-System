<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$sql = "
  SELECT j.*,
         c.name      AS customer_name,
         v.brand     AS vehicle_brand,
         v.model     AS vehicle_model,
         e.name      AS employee_name
  FROM jobcard j
  JOIN customer c ON j.customer_id = c.id
  JOIN vehicle v  ON j.vehicle_id  = v.id
  JOIN employee e ON j.employee_id = e.id
  ORDER BY j.created_at DESC
";
$rows = $pdo->query($sql)->fetchAll();
$totalJobcards = count($rows);
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
  .jobcard-table {
    margin-top:24px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(15,23,42,0.05);
  }
  .jobcard-table table {
    width:100%;
    border-collapse:collapse;
  }
  .jobcard-table thead { background:#f8fafc; }
  .jobcard-table th,
  .jobcard-table td {
    padding:12px 16px;
    text-align:left;
  }
  .jobcard-table tbody tr {
    border-top:1px solid #f1f5f9;
  }
  .jobcard-actions a {
    margin-right:10px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
  }
  .jobcard-actions a.delete { color:#dc2626; }
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
    <h2>Job Cards</h2>
    <p><?= $totalJobcards ?> job cards recorded</p>
  </div>
  <a class="btn" href="add.php">+ New job card</a>
</div>

<div class="jobcard-table">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Customer</th>
        <th>Vehicle</th>
        <th>Assigned To</th>
        <th>Date In</th>
        <th>Status</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($totalJobcards === 0): ?>
        <tr>
          <td colspan="7" style="text-align:center;color:#94a3b8;padding:20px;">
            No job cards yet.
          </td>
        </tr>
      <?php endif; ?>

      <?php foreach ($rows as $r): ?>
        <tr>
          <td>#<?= htmlspecialchars($r['id']) ?></td>
          <td><?= htmlspecialchars($r['customer_name']) ?></td>
          <td><?= htmlspecialchars($r['vehicle_brand'] . ' ' . $r['vehicle_model']) ?></td>
          <td><?= htmlspecialchars($r['employee_name']) ?></td>
          <td><?= htmlspecialchars($r['date_in']) ?></td>
          <td>
            <span class="status-pill status-<?= htmlspecialchars($r['status']) ?>">
              <?= htmlspecialchars(str_replace('_',' ',$r['status'])) ?>
            </span>
          </td>
          <td class="jobcard-actions" style="text-align:right;">
            <a href="view.php?id=<?= $r['id'] ?>">View</a>
            <a href="edit.php?id=<?= $r['id'] ?>">Edit</a>
            <a
              class="delete"
              href="delete.php?id=<?= $r['id'] ?>"
              onclick="return confirm('Delete this job card? This cannot be undone.');"
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
