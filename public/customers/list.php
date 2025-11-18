<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$rows = $pdo->query("SELECT * FROM customer ORDER BY created_at DESC")->fetchAll();
?>
<div class="page-hero" style="display:flex;align-items:center;justify-content:space-between">
  <h2>Customers</h2>
  <a class="btn" href="add.php">+ Add customer</a>
</div>

<div class="card">
  <table class="table">
    <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" style="text-align:center;color:#6b7280;padding:18px">No customers yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['id'])?></td>
          <td><a href="view.php?id=<?=$r['id']?>"><?=htmlspecialchars($r['name'])?></a></td>
          <td><?=htmlspecialchars($r['phone'])?></td>
          <td><?=htmlspecialchars($r['email'])?></td>
          <td><?=htmlspecialchars($r['created_at'])?></td>
          <td class="actions">
            <a href="edit.php?id=<?=$r['id']?>">Edit</a>
            <a class="delete" href="delete.php?id=<?=$r['id']?>" onclick="return confirm('Delete this customer?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
