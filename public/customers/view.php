<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
$base = '/ams_project';

if ($requireAuth) {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  if (empty($_SESSION['user'])) {
    $redirectTarget = $base . '/public/login.php';
    if (!empty($_SERVER['REQUEST_URI'])) {
      $redirectTarget .= '?redirect=' . urlencode($_SERVER['REQUEST_URI']);
    }
    header("Location: $redirectTarget");
    exit;
  }
}

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location:list.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM customer WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) {
  header('Location:list.php');
  exit;
}

// handle quick purchase form submission (basic)
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_purchase'])) {
  $vehicle_id = intval($_POST['vehicle_id'] ?? 0) ?: null;
  $inventory_id = intval($_POST['inventory_id'] ?? 0) ?: null;
  $qty = max(1, intval($_POST['qty'] ?? 1));
  $coupon_id = intval($_POST['coupon_id'] ?? 0) ?: null;
  $discount_percent = floatval($_POST['discount'] ?? 0);
  $notes = trim($_POST['notes'] ?? '');

  // Fetch price based on selected vehicle or product
  $unit_price = 0;
  if ($vehicle_id) {
    $stmt = $pdo->prepare("SELECT price FROM vehicle WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $unit_price = $stmt->fetchColumn() ?: 0;
  } elseif ($inventory_id) {
    $stmt = $pdo->prepare("SELECT unit_price FROM inventory WHERE id = ?");
    $stmt->execute([$inventory_id]);
    $unit_price = $stmt->fetchColumn() ?: 0;
  }

  // compute subtotal and discount
  $subtotal = $unit_price * $qty;
  $discount_amount = ($discount_percent / 100.0) * $subtotal;

  if ($coupon_id && $inventory_id) { // Coupons only apply to products
    $cstmt = $pdo->prepare("SELECT * FROM coupon WHERE id = ? AND active = 1");
    $cstmt->execute([$coupon_id]);
    $coupon = $cstmt->fetch();
    if ($coupon) {
      // check validity
      $now = date('Y-m-d');
      if (($coupon['valid_from'] && $now < $coupon['valid_from']) || ($coupon['valid_to'] && $now > $coupon['valid_to'])) {
        $errors[] = 'Coupon not valid at this date';
      } elseif ($coupon['uses_count'] >= $coupon['uses_allowed']) {
        $errors[] = 'Coupon usage limit reached';
      } elseif ($subtotal < $coupon['min_purchase']) {
        $errors[] = 'Minimum purchase not met for coupon';
      } else {
        if ($coupon['discount_type'] === 'percent') {
          $discount_amount += ($coupon['discount_value'] / 100.0) * $subtotal;
        } else {
          $discount_amount += min($coupon['discount_value'], $subtotal);
        }
      }
    } else {
      $errors[] = 'Coupon not found or inactive';
    }
  }

  $total = max(0, $subtotal - $discount_amount);

  if (empty($errors)) {
    $stmt = $pdo->prepare("INSERT INTO purchase (customer_id, vehicle_id, inventory_id, qty, unit_price, discount_amount, coupon_id, total_amount, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $vehicle_id ?: null, $inventory_id ?: null, $qty, $unit_price, $discount_amount, $coupon_id ?: null, $total, $notes]);

    // if coupon used, increment usage
    if (!empty($coupon_id)) {
      $pdo->prepare("UPDATE coupon SET uses_count = uses_count + 1 WHERE id = ?")->execute([$coupon_id]);
    }

    // decrement inventory qty if inventory used
    if ($inventory_id) {
      $pdo->prepare("UPDATE inventory SET qty = qty - ? WHERE id = ?")->execute([$qty, $inventory_id]);
    }

    // auto-mark vehicle as sold if a vehicle is purchased
    if ($vehicle_id) {
      $pdo->prepare("UPDATE vehicle SET status = 'sold' WHERE id = ?")->execute([$vehicle_id]);
    }

    header("Location: view.php?id=$id");
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_purchase'])) {
  $vehicle_id = intval($_POST['vehicle_id'] ?? 0) ?: null;
  $inventory_id = intval($_POST['inventory_id'] ?? 0) ?: null;
  $qty = max(1, intval($_POST['qty'] ?? 1));
  $unit_price = floatval($_POST['unit_price'] ?? 0);
  $discount_percent = floatval($_POST['discount'] ?? 0);
  $notes = trim($_POST['notes'] ?? '');

  // Fetch price based on selected vehicle or product
  if ($vehicle_id) {
    $stmt = $pdo->prepare("SELECT price FROM vehicle WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $unit_price = $stmt->fetchColumn() ?: 0;
  } elseif ($inventory_id) {
    $stmt = $pdo->prepare("SELECT unit_price FROM inventory WHERE id = ?");
    $stmt->execute([$inventory_id]);
    $unit_price = $stmt->fetchColumn() ?: 0;
  }

  // compute subtotal and discount
  $subtotal = $unit_price * $qty;
  $discount_amount = ($discount_percent / 100.0) * $subtotal;
  $total = max(0, $subtotal - $discount_amount);

  $stmt = $pdo->prepare("INSERT INTO purchase (customer_id, vehicle_id, inventory_id, qty, unit_price, discount_amount, total_amount, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([$id, $vehicle_id ?: null, $inventory_id ?: null, $qty, $unit_price, $discount_amount, $total, $notes]);

  if ($vehicle_id) {
    $pdo->prepare("UPDATE vehicle SET status = 'sold' WHERE id = ?")->execute([$vehicle_id]);
  }

  header("Location: view.php?id=$id");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_purchase'])) {
  $purchase_id = intval($_POST['purchase_id'] ?? 0);
  if ($purchase_id) {
    $stmt = $pdo->prepare("DELETE FROM purchase WHERE id = ?");
    $stmt->execute([$purchase_id]);
  }

  header("Location: view.php?id=$id");
  exit;
}

// fetch purchases
$pstmt = $pdo->prepare("SELECT p.*, v.brand AS v_brand, v.model AS v_model, i.name AS item_name, c.code AS coupon_code
                        FROM purchase p
                        LEFT JOIN vehicle v ON p.vehicle_id = v.id
                        LEFT JOIN inventory i ON p.inventory_id = i.id
                        LEFT JOIN coupon c ON p.coupon_id = c.id
                        WHERE p.customer_id = ? ORDER BY p.purchase_date DESC");
$pstmt->execute([$id]);
$purchases = $pstmt->fetchAll();

// fetch simple lists for the purchase form
$vehicles = $pdo->query("SELECT id, brand, model, price FROM vehicle WHERE status = 'available'")->fetchAll();
$items = $pdo->query("SELECT id, name, qty, unit_price FROM inventory WHERE qty > 0")->fetchAll();
$coupons = $pdo->query("SELECT id, code, discount_type, discount_value FROM coupon WHERE active = 1")->fetchAll();

function formatValue(?string $value, string $fallback = 'Not provided'): string {
  if ($value === null) return $fallback;
  $trimmed = trim($value);
  if ($trimmed === '') return $fallback;
  return htmlspecialchars($trimmed);
}

function formatDateValue(?string $value, string $fallback = 'Not set'): string {
  if (!$value) return $fallback;
  $timestamp = strtotime($value);
  return $timestamp ? date('M j, Y', $timestamp) : htmlspecialchars($value);
}

function formatCurrencyValue($value, string $fallback = 'Not set'): string {
  if ($value === null || $value === '') return $fallback;
  return 'Rs ' . number_format((float)$value, 2);
}

$purchaseCount = count($purchases);
$totalSpend = array_sum(array_map(function ($p) { return (float)($p['total_amount'] ?? 0); }, $purchases));
$lastPurchaseDate = $purchaseCount ? ($purchases[0]['purchase_date'] ?? null) : null;

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
  .profile-hero {
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
  .profile-eyebrow {
    text-transform:uppercase;
    letter-spacing:0.1em;
    font-size:12px;
    color:#6366f1;
    margin:0 0 6px;
  }
  .profile-hero h2 {
    margin:0;
    font-size:30px;
    color:#0f172a;
  }
  .profile-subtitle {
    margin-top:6px;
    color:#475569;
    font-size:14px;
  }
  .profile-actions {
    display:flex;
    gap:10px;
    align-items:center;
  }
  .profile-actions .btn {
    background-color:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .profile-actions .btn.secondary {
    background:#fff;
    color:#2563eb;
    border:1px solid rgba(37,99,235,0.3);
  }
  .profile-summary {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:16px;
  }
  .summary-card {
    border-radius:14px;
    border:1px solid #e2e8f0;
    padding:16px;
    background:#fff;
    box-shadow:0 8px 30px rgba(15,23,42,0.05);
  }
  .summary-card .label {
    font-size:12px;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:0.08em;
  }
  .summary-card .value {
    display:block;
    margin-top:6px;
    font-size:22px;
    font-weight:600;
    color:#0f172a;
  }
  .summary-card .hint {
    font-size:13px;
    color:#94a3b8;
  }
  .profile-sections,
  .profile-forms {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:18px;
  }
  .profile-card.detail {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:22px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .profile-card.detail h3 {
    margin-top:0;
    color:#0f172a;
  }
  .profile-card.detail dl {
    margin:0;
  }
  .profile-card.detail dt {
    font-size:12px;
    text-transform:uppercase;
    color:#94a3b8;
    letter-spacing:0.08em;
    margin-top:14px;
  }
  .profile-card.detail dd {
    margin:4px 0 0;
    font-size:15px;
    color:#0f172a;
  }
  .history-list {
    display:flex;
    flex-direction:column;
    gap:14px;
  }
  .history-row {
    padding-bottom:14px;
    border-bottom:1px dashed #e2e8f0;
  }
  .history-head {
    display:flex;
    justify-content:space-between;
    gap:10px;
    font-weight:600;
    color:#111827;
  }
  .history-meta {
    font-size:14px;
    color:#64748b;
    margin-top:4px;
  }
  .history-meta form {
    display:inline;
  }
  .coupon-chip {
    display:inline-block;
    padding:6px 10px;
    border-radius:999px;
    background:#eef2ff;
    color:#4338ca;
    font-size:12px;
    margin-top:4px;
  }
  .form-card .form-row {
    margin-bottom:12px;
  }
  .profile-card button.btn {
    background-color:#2563eb;
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:5px;
    cursor:pointer;
  }
  .coupon-list > div {
    padding:10px 0;
    border-bottom:1px dashed #e2e8f0;
  }
  .coupon-list > div:last-child {
    border-bottom:none;
  }
</style>

<div class="profile-hero">
  <div>
    <p class="profile-eyebrow">Customer Profile</p>
    <h2><?=htmlspecialchars($c['name'])?></h2>
    <p class="profile-subtitle">
      <?=formatValue($c['email'], 'No email on file')?> &middot;
      <?=formatValue($c['phone'], 'No phone on file')?>
    </p>
  </div>
  <div class="profile-actions">
    <a class="btn" href="list.php">Back to customers</a>
  </div>
</div>

<div class="profile-summary">
  <div class="summary-card">
    <span class="label">Customer ID</span>
    <span class="value">#<?=htmlspecialchars($c['id'])?></span>
    <span class="hint">Internal reference</span>
  </div>
  <div class="summary-card">
    <span class="label">Total Purchases</span>
    <span class="value"><?=$purchaseCount?></span>
    <span class="hint">Orders recorded</span>
  </div>
  <div class="summary-card">
    <span class="label">Lifetime Spend</span>
    <span class="value"><?=formatCurrencyValue($totalSpend, 'Rs 0.00')?></span>
    <span class="hint">Across all purchases</span>
  </div>
  <div class="summary-card">
    <span class="label">Last Purchase</span>
    <span class="value"><?=formatDateValue($lastPurchaseDate, 'No history')?></span>
    <span class="hint">Most recent</span>
  </div>
</div>

<div class="profile-sections">
  <section class="profile-card detail">
    <h3>Contact Details</h3>
    <dl>
      <dt>Name</dt>
      <dd><?=htmlspecialchars($c['name'])?></dd>
      <dt>Phone</dt>
      <dd><?=formatValue($c['phone'], 'Not provided')?></dd>
      <dt>Email</dt>
      <dd><?=formatValue($c['email'], 'Not provided')?></dd>
      <dt>Address</dt>
      <dd><?=nl2br(formatValue($c['address'] ?? '', 'Not provided'))?></dd>
      <dt>Notes</dt>
      <dd><?=nl2br(formatValue($c['notes'] ?? '', 'No notes'))?></dd>
    </dl>
  </section>

  <section class="profile-card detail">
    <h3>Purchase History</h3>
    <?php if (empty($purchases)): ?>
      <div style="color:#94a3b8">No purchase history.</div>
    <?php else: ?>
      <div class="history-list">
        <?php foreach ($purchases as $p): ?>
          <div class="history-row">
            <div class="history-head">
              <span><?= $p['vehicle_id'] ? htmlspecialchars($p['v_brand'].' '.$p['v_model']) : htmlspecialchars($p['item_name'] ?? 'Item') ?></span>
              <span style="text-align:right;color:#64748b">
                <?=htmlspecialchars($p['purchase_date'])?>
                <form method="post">
                  <input type="hidden" name="delete_purchase" value="1">
                  <input type="hidden" name="purchase_id" value="<?=htmlspecialchars($p['id'])?>">
                  <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer">Delete</button>
                </form>
              </span>
            </div>
            <div class="history-meta">
              Qty: <?=htmlspecialchars($p['qty'])?> &middot;
              Unit: <?=number_format($p['unit_price'],2)?> &middot;
              Discount: <?=number_format($p['discount_amount'],2)?> &middot;
              Total: <?=number_format($p['total_amount'],2)?>
            </div>
            <?php if (!empty($p['coupon_code'])): ?>
              <span class="coupon-chip">Coupon: <?=htmlspecialchars($p['coupon_code'])?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</div>

<div class="profile-forms">
  <section class="profile-card detail form-card">
    <h3>Record Purchase</h3>
    <?php if (!empty($errors)): ?>
      <div style="color:#b91c1c;margin-bottom:10px"><?php foreach($errors as $e) echo htmlspecialchars($e).'<br>'; ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="record_purchase" value="1">
      <div class="form-row">
        <label>Vehicle (optional)</label>
        <select name="vehicle_id">
          <option value="">-- select vehicle (if sale) --</option>
          <?php foreach($vehicles as $v): ?>
            <option value="<?=$v['id']?>"><?=htmlspecialchars($v['brand'].' '.$v['model'].' - '.number_format($v['price'],2))?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <label>Inventory item (optional)</label>
        <select name="inventory_id">
          <option value="">-- select item --</option>
          <?php foreach($items as $it): ?>
            <option value="<?=$it['id']?>"><?=htmlspecialchars($it['name'].' ('.$it['qty'].' in stock)')?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row"><label>Quantity</label><input type="number" name="qty" value="1" min="1"></div>

      <div class="form-row"><label>Coupon (optional)</label>
        <select name="coupon_id">
          <option value="">-- apply coupon --</option>
          <?php foreach($coupons as $cp): ?>
            <option value="<?=$cp['id']?>"><?=htmlspecialchars($cp['code'].' - '.($cp['discount_type']=='percent' ? $cp['discount_value'].'%' : number_format($cp['discount_value'],2)))?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row"><label>Discount (%)</label>
        <div style="display:flex;align-items:center;">
          <input type="number" step="0.01" name="discount" value="0" style="flex:1;">
          <span style="margin-left:8px;">%</span>
        </div>
      </div>
      <div class="form-row"><label>Notes</label><textarea name="notes"></textarea></div>

      <div style="margin-top:8px"><button class="btn" type="submit">Record purchase</button></div>
    </form>
  </section>

  <section class="profile-card detail">
    <h3>Available Coupons</h3>
    <?php
      $allc = $pdo->query("SELECT code, discount_type, discount_value, valid_from, valid_to FROM coupon WHERE active = 1")->fetchAll();
      if (empty($allc)) {
        echo '<div style="color:#94a3b8">No coupons available.</div>';
      } else {
        echo '<div class="coupon-list">';
        foreach($allc as $cc) {
          echo '<div>';
          echo '<div style="font-weight:700">'.htmlspecialchars($cc['code']).' <span style="font-weight:600;color:#64748b;font-size:13px">('.($cc['discount_type']=='percent' ? $cc['discount_value'].'%' : number_format($cc['discount_value'],2)).')</span></div>';
          echo '<div style="color:#94a3b8;font-size:13px">Valid: '.htmlspecialchars($cc['valid_from'] ?? 'N/A').' - '.htmlspecialchars($cc['valid_to'] ?? 'N/A').'</div>';
          echo '</div>';
        }
        echo '</div>';
      }
    ?>
  </section>
</div>

<section class="profile-card detail" style="margin-top:24px;">
  <h3>Add Purchase</h3>
  <form method="post">
    <input type="hidden" name="add_purchase" value="1">
    <div class="form-row">
      <label>Vehicle (optional)</label>
      <select name="vehicle_id">
        <option value="">-- select vehicle --</option>
        <?php foreach($vehicles as $v): ?>
          <option value="<?=$v['id']?>"><?=htmlspecialchars($v['brand'].' '.$v['model'].' - '.number_format($v['price'],2))?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row">
      <label>Inventory item (optional)</label>
      <select name="inventory_id">
        <option value="">-- select item --</option>
        <?php foreach($items as $it): ?>
          <option value="<?=$it['id']?>"><?=htmlspecialchars($it['name'].' ('.$it['qty'].' in stock)')?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-row"><label>Quantity</label><input type="number" name="qty" value="1" min="1"></div>
    <div class="form-row"><label>Discount (%)</label><input type="number" step="0.01" name="discount" value="0"></div>
    <div class="form-row"><label>Notes</label><textarea name="notes"></textarea></div>

    <div style="margin-top:8px"><button class="btn" type="submit">Add Purchase</button></div>
  </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>





