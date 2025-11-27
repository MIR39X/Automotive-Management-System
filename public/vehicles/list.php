<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$rows = $pdo->query("SELECT * FROM vehicle ORDER BY created_at DESC")->fetchAll();

$totalVehicles = count($rows);
$available = count(array_filter($rows, fn($v) => $v['status'] === 'available'));
$sold = count(array_filter($rows, fn($v) => $v['status'] === 'sold'));
$inventoryValue = array_sum(array_map(fn($v) => (float)($v['price'] ?? 0), $rows));

function formatCurrency($value): string {
  return 'Rs ' . number_format((float)$value, 2);
}
?>

<style>
  .vehicle-hero {
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
  .vehicle-hero h2 {
    margin:0;
    font-size:30px;
    color:#0f172a;
  }
  .vehicle-hero p {
    margin:6px 0 0;
    color:#475569;
  }
  .vehicle-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .vehicle-summary {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
  }
  .vehicle-summary .card {
    border:1px solid #e2e8f0;
    border-radius:12px;
    padding:16px;
    background:#fff;
    box-shadow:0 8px 30px rgba(15,23,42,0.05);
  }
  .vehicle-summary .label {
    font-size:12px;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:0.08em;
  }
  .vehicle-summary .value {
    display:block;
    margin-top:6px;
    font-size:22px;
    font-weight:600;
    color:#0f172a;
  }
  .vehicle-table {
    margin-top:24px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(15,23,42,0.05);
  }
  .vehicle-table table {
    width:100%;
    border-collapse:collapse;
  }
  .vehicle-table thead {
    background:#f8fafc;
  }
  .vehicle-table th,
  .vehicle-table td {
    padding:14px 16px;
    text-align:left;
  }
  .vehicle-table tbody tr {
    border-top:1px solid #f1f5f9;
  }
  .vehicle-thumb {
    width:80px;
    height:52px;
    border-radius:10px;
    border:1px solid #e2e8f0;
    background:#f8fafc;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    font-size:12px;
    color:#94a3b8;
  }
  .vehicle-thumb img {
    width:100%;
    height:100%;
    object-fit:cover;
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
  .status-available { background:#dcfce7;color:#15803d; }
  .status-sold { background:#fee2e2;color:#b91c1c; }
  .status-service { background:#fef3c7;color:#92400e; }
  .vehicle-actions a {
    margin-right:10px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
  }
  .vehicle-actions a.delete {
    color:#dc2626;
  }
</style>

<div class="vehicle-hero">
  <div>
    <h2>Vehicles</h2>
    <p><?=$totalVehicles?> vehicles in inventory</p>
  </div>
  <a class="btn" href="add.php">+ Add vehicle</a>
</div>

<div class="vehicle-summary">
  <div class="card">
    <span class="label">Total Vehicles</span>
    <span class="value"><?=$totalVehicles?></span>
  </div>
  <div class="card">
    <span class="label">Available</span>
    <span class="value"><?=$available?></span>
  </div>
  <div class="card">
    <span class="label">Sold</span>
    <span class="value"><?=$sold?></span>
  </div>
  <div class="card">
    <span class="label">Inventory Value</span>
    <span class="value"><?=formatCurrency($inventoryValue)?></span>
  </div>
</div>

<div class="vehicle-table">
  <table role="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Vehicle</th>
        <th>VIN</th>
        <th>Year</th>
        <th>Price</th>
        <th>Status</th>
        <th style="text-align:right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($totalVehicles === 0): ?>
        <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:20px">No vehicles yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <?php
          $vehicleLabel = trim(($r['brand'] ?? '') . ' ' . ($r['model'] ?? ''));
          $confirmMessage = $vehicleLabel !== ''
            ? "Delete {$vehicleLabel}? This action cannot be undone."
            : "Delete this vehicle? This action cannot be undone.";
        ?>
        <tr>
          <td>#<?=htmlspecialchars($r['id'])?></td>
          <td>
            <div style="display:flex;align-items:center;gap:14px;">
              <div class="vehicle-thumb">
                <?php if (!empty($r['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $r['image'])): ?>
                  <img src="/ams_project/assets/uploads/<?=htmlspecialchars($r['image'])?>" alt="">
                <?php else: ?>
                  No Image
                <?php endif; ?>
              </div>
              <div>
                <div style="font-weight:600;color:#0f172a"><?=htmlspecialchars($r['brand'].' '.$r['model'])?></div>
                <div style="font-size:13px;color:#94a3b8">ID <?=htmlspecialchars($r['id'])?></div>
              </div>
            </div>
          </td>
          <td><?=htmlspecialchars($r['vin'])?></td>
          <td><?=htmlspecialchars($r['year'])?></td>
          <td><?=formatCurrency($r['price'])?></td>
          <td>
            <span class="status-pill status-<?=htmlspecialchars($r['status'])?>"><?=htmlspecialchars($r['status'])?></span>
          </td>
          <td class="vehicle-actions" style="text-align:right;">
            <a href="view.php?id=<?=$r['id']?>">View</a>
            <a href="edit.php?id=<?=$r['id']?>">Edit</a>
            <a
              class="delete"
              href="delete.php?id=<?=$r['id']?>"
              data-confirm="<?=htmlspecialchars($confirmMessage, ENT_QUOTES)?>"
              data-confirm-title="Remove vehicle"
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





