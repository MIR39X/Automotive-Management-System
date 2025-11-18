<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$rows = $pdo->query("SELECT id, name, cnic, salary, role, phone, hire_date FROM employee ORDER BY created_at DESC")->fetchAll();
?>
<div class="page-hero" style="display:flex;align-items:center;justify-content:space-between;background-color:#f3f4f6;padding:20px;border-radius:8px">
  <h2 style="margin:0;color:#111827">Employees</h2>
  <a class="btn" href="add.php" style="background-color:#2563eb;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none">+ Add employee</a>
</div>

<div class="card" style="margin-top:20px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">
  <table class="table" style="width:100%;border-collapse:collapse">
    <thead style="background-color:#f9fafb;border-bottom:2px solid #e5e7eb">
      <tr>
        <th style="padding:10px;text-align:left;color:#6b7280">ID</th>
        <th style="padding:10px;text-align:left;color:#6b7280">Name</th>
        <th style="padding:10px;text-align:left;color:#6b7280">CNIC</th>
        <th style="padding:10px;text-align:left;color:#6b7280">Salary</th>
        <th style="padding:10px;text-align:left;color:#6b7280">Role</th>
        <th style="padding:10px;text-align:left;color:#6b7280">Phone</th>
        <th style="padding:10px;text-align:left;color:#6b7280">Hire Date</th>
        <th style="padding:10px;text-align:left;color:#6b7280">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" style="text-align:center;color:#6b7280;padding:18px">No employees yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr style="border-bottom:1px solid #e5e7eb">
          <td style="padding:10px;color:#111827"><?=htmlspecialchars($r['id'])?></td>
          <td style="padding:10px;color:#111827"><a href="view.php?id=<?=$r['id']?>" style="color:#2563eb;text-decoration:none"><?=htmlspecialchars($r['name'])?></a></td>
          <td style="padding:10px;color:#111827"><?=htmlspecialchars($r['cnic'])?></td>
          <td style="padding:10px;color:#111827"><?=htmlspecialchars($r['salary'])?></td>
          <td style="padding:10px;color:#111827"><?=htmlspecialchars($r['role'])?></td>
          <td style="padding:10px;color:#111827"><?=htmlspecialchars($r['phone'])?></td>
          <td style="padding:10px;color:#111827"><?=htmlspecialchars($r['hire_date'])?></td>
          <td style="padding:10px;color:#2563eb">
            <a href="edit.php?id=<?=$r['id']?>" style="margin-right:10px;color:#2563eb;text-decoration:none">Edit</a>
            <a class="delete" href="delete.php?id=<?=$r['id']?>" onclick="return confirm('Delete this employee?')" style="color:#dc2626;text-decoration:none">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>