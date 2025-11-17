<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM vehicle WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
  header('Location: list.php');
  exit;
}
?>

<div class="card" style="padding:20px;">
  <div style="display:flex;align-items:center;justify-content:space-between">
    <h2 style="margin:0;font-size:24px;color:#333">Vehicle Details</h2>
    <div><a class="btn" href="list.php" style="background-color:#007BFF;color:#fff;padding:10px 15px;border-radius:5px;text-decoration:none">Back to vehicles</a></div>
  </div>
</div>

<div class="card" style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
  <div style="display:flex;gap:30px">
    <div style="flex:1;">
      <?php if (!empty($row['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $row['image'])): ?>
        <img src="/ams_project/assets/uploads/<?=htmlspecialchars($row['image'])?>" alt="<?=htmlspecialchars($row['brand'].' '.$row['model'])?>" style="width:100%;max-width:400px;border:1px solid #e6e9ef;padding:6px;border-radius:6px">
      <?php else: ?>
        <div style="width:400px;height:300px;display:flex;align-items:center;justify-content:center;color:#bbb;border:1px dashed #eee;border-radius:6px">No Image</div>
      <?php endif; ?>
    </div>
    <div style="flex:2;">
      <p style="font-size:18px;color:#555;margin:0 0 10px"><strong>VIN:</strong> <?=htmlspecialchars($row['vin'])?></p>
      <p style="font-size:18px;color:#555;margin:0 0 10px"><strong>Brand:</strong> <?=htmlspecialchars($row['brand'])?></p>
      <p style="font-size:18px;color:#555;margin:0 0 10px"><strong>Model:</strong> <?=htmlspecialchars($row['model'])?></p>
      <p style="font-size:18px;color:#555;margin:0 0 10px"><strong>Year:</strong> <?=htmlspecialchars($row['year'])?></p>
      <p style="font-size:18px;color:#555;margin:0 0 10px"><strong>Price:</strong> $<?=number_format($row['price'], 2)?></p>
      <p style="font-size:18px;color:#555;margin:0 0 10px"><strong>Status:</strong> <span style="background-color:#e9ecef;padding:5px 10px;border-radius:5px;color:#333"><?=htmlspecialchars($row['status'])?></span></p>
      <p style="font-size:18px;color:#555;margin:0 0 10px"><strong>Description:</strong></p>
      <p style="font-size:16px;color:#777;margin:0;line-height:1.6;">"<?=htmlspecialchars($row['description'])?>"</p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>