<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$rows = $pdo->query("SELECT * FROM vehicle ORDER BY created_at DESC")->fetchAll();
?>
<div class="card" style="padding:20px;">
  <div style="display:flex;align-items:center;justify-content:space-between">
    <h2 style="margin:0;font-size:24px;color:#333">Vehicles</h2>
    <div>
      <a class="btn" href="add.php" style="background-color:#007BFF;color:#fff;padding:10px 15px;border-radius:5px;text-decoration:none">+ Add vehicle</a>
    </div>
  </div>
</div>

<div class="card" style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
  <table class="table" role="table" style="width:100%;border-collapse:collapse">
    <thead>
      <tr style="background-color:#f8f9fa;border-bottom:2px solid #ddd">
        <th style="padding:10px;text-align:left;color:#333">ID</th>
        <th style="padding:10px;text-align:left;color:#333"></th>
        <th style="padding:10px;text-align:left;color:#333">VIN</th>
        <th style="padding:10px;text-align:left;color:#333">Brand</th>
        <th style="padding:10px;text-align:left;color:#333">Model</th>
        <th style="padding:10px;text-align:left;color:#333">Year</th>
        <th style="padding:10px;text-align:left;color:#333">Price</th>
        <th style="padding:10px;text-align:left;color:#333">Status</th>
        <th style="padding:10px;text-align:left;color:#333">Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (count($rows) === 0): ?>
      <tr><td colspan="9" style="text-align:center;color:#666;padding:18px">No vehicles yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr style="border-bottom:1px solid #ddd">
        <td style="padding:10px;color:#555"><?=htmlspecialchars($r['id'])?></td>
        <td style="width:100px;padding:10px">
          <?php if (!empty($r['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $r['image'])): ?>
            <img src="/ams_project/assets/uploads/<?=htmlspecialchars($r['image'])?>" alt="" style="max-width:86px;border-radius:6px;border:1px solid #eee;padding:3px">
          <?php else: ?>
            <div style="width:86px;height:54px;display:flex;align-items:center;justify-content:center;color:#bbb;border:1px dashed #eee;border-radius:6px">No Image</div>
          <?php endif; ?>
        </td>
        <td style="padding:10px;color:#555"><?=htmlspecialchars($r['vin'])?></td>
        <td style="padding:10px;color:#555"><?=htmlspecialchars($r['brand'])?></td>
        <td style="padding:10px;color:#555"><?=htmlspecialchars($r['model'])?></td>
        <td style="padding:10px;color:#555"><?=htmlspecialchars($r['year'])?></td>
        <td style="padding:10px;color:#555">$<?=number_format($r['price'], 2)?></td>
        <td style="padding:10px;color:#555">
          <span style="background-color:#e9ecef;padding:5px 10px;border-radius:5px;color:#333"><?=htmlspecialchars($r['status'])?></span>
        </td>
        <td style="padding:10px;color:#007BFF">
          <a href="view.php?id=<?= $r['id'] ?>" style="color:#007BFF;text-decoration:none">View</a>
          <a href="edit.php?id=<?= $r['id'] ?>" style="color:#007BFF;text-decoration:none;margin-left:10px">Edit</a>
          <a href="delete.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete this vehicle?')" style="color:#dc3545;text-decoration:none;margin-left:10px">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
