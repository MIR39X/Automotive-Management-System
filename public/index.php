<?php
// public/index.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
?>
<!-- load page-specific stylesheet (scoped) -->
<style>@import url('/ams_project/assets/css/index.css');</style>

<?php
// fetch vehicles
$stmt = $pdo->query("SELECT id, vin, brand, model, year, price, status, image FROM vehicle ORDER BY created_at DESC");
$vehicles = $stmt->fetchAll();
?>
<div id="front-page">
  <div class="page-hero" style="margin-top:6px;">
    <h2>Available Vehicles</h2>
  </div>

  <div class="card">
    <?php if (count($vehicles) === 0): ?>
      <div style="padding:40px;text-align:center;color:#6b7280">
        No vehicles found. Add some from the admin area.
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($vehicles as $v): ?>
          <div class="vehicle-card">
            <div class="v-image">
              <?php if (!empty($v['image']) && file_exists(__DIR__ . '/../assets/uploads/' . $v['image'])): ?>
                <img src="/ams_project/assets/uploads/<?=htmlspecialchars($v['image'])?>" alt="<?=htmlspecialchars($v['brand'].' '.$v['model'])?>">
              <?php else: ?>
                <div style="color:#c5cbd4;font-size:14px">No Image</div>
              <?php endif; ?>

              <div class="overlay">
                <div class="cta">
                  <a href="/ams_project/public/vehicles/view.php?id=<?= $v['id'] ?>" class="primary">View</a>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                  <div class="title"><?=htmlspecialchars($v['brand'])?> <?=htmlspecialchars($v['model'])?></div>
                  <div class="meta">VIN: <?=htmlspecialchars($v['vin'])?></div>
                </div>
                <div style="text-align:right">
                  <div style="font-weight:700;color:#0f1724"><?=number_format($v['price'],2)?></div>
                  <div class="meta"><?=htmlspecialchars($v['year'])?></div>
                </div>
              </div>

              <div style="display:flex;align-items:center;justify-content:space-between">
                <div class="meta">Status</div>
                <div><span class="status"><?=htmlspecialchars($v['status'])?></span></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
