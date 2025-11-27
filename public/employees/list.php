<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$rows = $pdo->query("SELECT id, name, cnic, salary, role, phone, hire_date FROM employee ORDER BY created_at DESC")->fetchAll();
$totalEmployees = count($rows);
$averageSalary = $totalEmployees ? array_sum(array_column($rows, 'salary')) / $totalEmployees : 0;
$recentHire = $totalEmployees ? $rows[0]['hire_date'] : null;

function formatDateValue(?string $value): string {
  if (!$value) return 'Not set';
  $timestamp = strtotime($value);
  return $timestamp ? date('M j, Y', $timestamp) : htmlspecialchars($value);
}

function formatCurrencyValue($value): string {
  return 'Rs ' . number_format((float)$value, 2);
}
?>

<style>
  .employee-hero {
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
  .employee-hero h2 {
    margin:0;
    font-size:30px;
    color:#0f172a;
  }
  .employee-hero p {
    margin:6px 0 0;
    color:#475569;
  }
  .employee-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .employee-summary {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
  }
  .employee-summary .card {
    border:1px solid #e2e8f0;
    border-radius:12px;
    padding:16px;
    background:#fff;
    box-shadow:0 8px 30px rgba(15,23,42,0.05);
  }
  .employee-summary .label {
    font-size:12px;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:0.08em;
  }
  .employee-summary .value {
    display:block;
    margin-top:6px;
    font-size:22px;
    font-weight:600;
    color:#0f172a;
  }
  .employee-table {
    margin-top:24px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(15,23,42,0.05);
  }
  .employee-table table {
    width:100%;
    border-collapse:collapse;
  }
  .employee-table thead {
    background:#f8fafc;
  }
  .employee-table th,
  .employee-table td {
    padding:14px 16px;
    text-align:left;
  }
  .employee-table tbody tr {
    border-top:1px solid #f1f5f9;
  }
  .employee-actions a {
    margin-right:10px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
  }
  .employee-actions a.delete {
    color:#dc2626;
  }
</style>

<div class="employee-hero">
  <div>
    <h2>Employees</h2>
    <p><?=$totalEmployees?> team members in the system</p>
  </div>
  <a class="btn" href="add.php">+ Add employee</a>
</div>

<div class="employee-summary">
  <div class="card">
    <span class="label">Total Employees</span>
    <span class="value"><?=$totalEmployees?></span>
  </div>
  <div class="card">
    <span class="label">Average Salary</span>
    <span class="value"><?=formatCurrencyValue($averageSalary)?></span>
  </div>
  <div class="card">
    <span class="label">Last Hire Date</span>
    <span class="value"><?=formatDateValue($recentHire)?></span>
  </div>
</div>

<div class="employee-table">
  <table role="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>CNIC</th>
        <th>Salary</th>
        <th>Role</th>
        <th>Phone</th>
        <th>Hire Date</th>
        <th style="text-align:right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:20px">No employees yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <?php
          $employeeName = trim($r['name'] ?? '');
          $employeeConfirm = $employeeName !== ''
            ? "Remove {$employeeName}? Their profile and history will be deleted."
            : "Delete this employee? Their profile and history will be deleted.";
        ?>
        <tr>
          <td>#<?=htmlspecialchars($r['id'])?></td>
          <td style="font-weight:600;color:#0f172a;">
            <a href="view.php?id=<?=$r['id']?>" style="color:#2563eb;text-decoration:none"><?=htmlspecialchars($r['name'])?></a>
          </td>
          <td><?=htmlspecialchars($r['cnic'])?></td>
          <td><?=formatCurrencyValue($r['salary'])?></td>
          <td><?=htmlspecialchars($r['role'])?></td>
          <td><?=htmlspecialchars($r['phone'])?></td>
          <td><?=formatDateValue($r['hire_date'])?></td>
          <td class="employee-actions" style="text-align:right;">
            <a href="edit.php?id=<?=$r['id']?>">Edit</a>
            <a
              class="delete"
              href="delete.php?id=<?=$r['id']?>"
              data-confirm="<?=htmlspecialchars($employeeConfirm, ENT_QUOTES)?>"
              data-confirm-title="Delete employee"
              data-confirm-cta="Delete"
              data-confirm-style="danger"
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


