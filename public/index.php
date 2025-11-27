<?php
// public/index.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
$userLoggedIn = !empty($isAuthenticated);
?>
<!-- load page-specific stylesheet (scoped) -->
<style>
  @import url('/ams_project/assets/css/index.css');
  .landing-hero h1 {
    margin:0;
    font-size:34px;
    color:#fff;
  }
  .landing-hero p {
    margin:12px 0 0;
    color:#c7d2fe;
    max-width:520px;
  }
  .highlight-stats {
    margin-top:-60px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    position:relative;
    z-index:2;
  }
  .stat-card {
    padding:22px;
    border-radius:20px;
    background:#fff;
    border:1px solid rgba(15,23,42,0.08);
    box-shadow:0 25px 60px rgba(15,23,42,0.08);
    transition:transform 0.2s ease, box-shadow 0.2s ease;
  }
  .stat-card:hover { transform:translateY(-4px);box-shadow:0 35px 70px rgba(15,23,42,0.12); }
  .stat-card .label {
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:#7c85a2;
  }
  .stat-card .value {
    margin-top:10px;
    font-size:32px;
    font-weight:700;
    color:#0f172a;
    display:block;
  }
  .inventory-section {
    margin-top:30px;
    border-radius:20px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 15px 45px rgba(15,23,42,0.08);
  }
  .inventory-section .section-head {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:20px;
    flex-wrap:wrap;
    gap:12px;
  }
  .inventory-section .section-head h2 {
    margin:0;
    font-size:26px;
    color:#0f172a;
  }
  .inventory-section .section-head a {
    color:#2563eb;
    text-decoration:none;
    font-weight:600;
  }
  .status-pill {
    display:inline-flex;
    align-items:center;
    padding:4px 12px;
    border-radius:999px;
    font-size:13px;
    text-transform:capitalize;
    font-weight:600;
  }
  .status-pill.available { background:#dcfce7;color:#15803d; }
  .status-pill.sold { background:#fee2e2;color:#b91c1c; }
  .status-pill.service { background:#fef3c7;color:#92400e; }
  .empty-state {
    padding:50px;
    text-align:center;
    color:#94a3b8;
  }
</style>

<?php
// fetch vehicles
$stmt = $pdo->query("SELECT id, vin, brand, model, year, price, status, image FROM vehicle ORDER BY created_at DESC");
$vehicles = $stmt->fetchAll();
$totalVehicles = count($vehicles);
$available = count(array_filter($vehicles, fn($v) => ($v['status'] ?? '') === 'available'));
$sold = count(array_filter($vehicles, fn($v) => ($v['status'] ?? '') === 'sold'));
$inventoryValue = array_sum(array_map(fn($v) => (float)($v['price'] ?? 0), $vehicles));

function formatCurrency($value): string {
  return 'Rs ' . number_format((float)$value, 2);
}
?>
<div id="front-page">
  <?php if ($userLoggedIn): ?>
    <div class="highlight-stats" style="margin-top:10px;">
      <div class="stat-card">
        <span class="label">Total Inventory</span>
        <span class="value"><?=$totalVehicles?></span>
      </div>
      <div class="stat-card">
        <span class="label">Available</span>
        <span class="value"><?=$available?></span>
      </div>
      <div class="stat-card">
        <span class="label">Sold</span>
        <span class="value"><?=$sold?></span>
      </div>
      <div class="stat-card">
        <span class="label">Inventory Value</span>
        <span class="value"><?=formatCurrency($inventoryValue)?></span>
      </div>
    </div>
  <?php else: ?>
    <div class="guest-banner card" style="margin-top:16px;padding:20px;text-align:center;">
      <strong>Guest view.</strong> Sign in to see inventory stats and manage vehicles.
      <div style="margin-top:10px;">
        <a class="btn" href="<?= $base ?>/public/login.php" style="background:#2563eb;color:#fff;padding:8px 20px;border-radius:8px;text-decoration:none;">Go to login</a>
      </div>
    </div>
  <?php endif; ?>

  <div class="inventory-section">
    <div class="section-head">
      <h2>Live Inventory</h2>
      <a href="/ams_project/public/vehicles/list.php">View dashboard →</a>
    </div>

    <?php if ($totalVehicles === 0): ?>
      <div class="empty-state">
        No vehicles found. Add some from the admin area to populate this grid.
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
                  <a href="/ams_project/public/vehicles/view.php?id=<?=$v['id']?>" class="primary">View</a>
                </div>
              </div>
            </div>

            <div class="card-body">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div>
                  <div class="title"><?=htmlspecialchars($v['brand'])?> <?=htmlspecialchars($v['model'])?></div>
                  <div class="meta">VIN: <?=htmlspecialchars($v['vin'])?></div>
                </div>
                <div style="text-align:right">
                  <div style="font-weight:700;color:#0f1724"><?=formatCurrency($v['price'])?></div>
                  <div class="meta"><?=htmlspecialchars($v['year'])?></div>
                </div>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between">
                <div class="meta">Status</div>
                <span class="status-pill <?=htmlspecialchars($v['status'])?>"><?=htmlspecialchars($v['status'])?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
