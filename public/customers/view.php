<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location:list.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM customer WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { echo '<div class="card">Customer not found</div>'; require_once __DIR__ . '/../../includes/footer.php'; exit; }

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
?>

<div class="page-hero" style="display:flex;align-items:center;justify-content:space-between">
  <h2><?=htmlspecialchars($c['name'])?></h2>
  <div>
    <a class="btn" href="list.php">Back</a>
    <a class="btn" href="/ams_project/public/index.php" style="margin-left:8px;background:#fff;color:var(--accent);border:1px solid var(--border)">Home</a>
  </div>
</div>

<div class="card" style="display:grid;grid-template-columns: 1fr 420px; gap:18px;">
  <div>
    <h3>Profile</h3>
    <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid var(--border)">
      <div><strong>Name:</strong> <?=htmlspecialchars($c['name'])?></div>
      <div><strong>Phone:</strong> <?=htmlspecialchars($c['phone'])?></div>
      <div><strong>Email:</strong> <?=htmlspecialchars($c['email'])?></div>
      <div><strong>Address:</strong> <?=nl2br(htmlspecialchars($c['address']))?></div>
      <div><strong>Notes:</strong><br><?=nl2br(htmlspecialchars($c['notes'] ?? ''))?></div>
    </div>

    <h3 style="margin-top:16px">Purchase history</h3>
    <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid var(--border)">
      <?php if (empty($purchases)): ?>
        <div style="color:var(--muted)">No purchase history.</div>
      <?php else: foreach ($purchases as $p): ?>
        <div style="padding:8px 0;border-bottom:1px dashed #f0f2f5">
          <div style="display:flex;justify-content:space-between">
            <div style="font-weight:700">
              <?= $p['vehicle_id'] ? htmlspecialchars($p['v_brand'].' '.$p['v_model']) : htmlspecialchars($p['item_name'] ?? 'Item') ?>
            </div>
            <div style="text-align:right;color:var(--muted)">
              <?=htmlspecialchars($p['purchase_date'])?>
              <form method="post" style="display:inline">
                <input type="hidden" name="delete_purchase" value="1">
                <input type="hidden" name="purchase_id" value="<?=htmlspecialchars($p['id'])?>">
                <button type="submit" style="background:none;border:none;color:#dc3545;cursor:pointer">Delete</button>
              </form>
            </div>
          </div>
          <div style="color:var(--muted);font-size:14px">
            Qty: <?=htmlspecialchars($p['qty'])?> • Unit: <?=number_format($p['unit_price'],2)?> • Discount: <?=number_format($p['discount_amount'],2)?> • Total: <?=number_format($p['total_amount'],2)?>
            <?php if (!empty($p['coupon_code'])): ?> • Coupon: <?=htmlspecialchars($p['coupon_code'])?> <?php endif; ?>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div>
    <h3>Record Purchase</h3>
    <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid var(--border)">
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
              <option value="<?=$v['id']?>"><?=htmlspecialchars($v['brand'].' '.$v['model'].' — '.number_format($v['price'],2))?></option>
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
              <option value="<?=$cp['id']?>"><?=htmlspecialchars($cp['code'].' — '.($cp['discount_type']=='percent' ? $cp['discount_value'].'%' : number_format($cp['discount_value'],2)))?></option>
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
    </div>

    <h3 style="margin-top:18px">Available coupons</h3>
    <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid var(--border)">
      <?php
        $allc = $pdo->query("SELECT code, discount_type, discount_value, valid_from, valid_to FROM coupon WHERE active = 1")->fetchAll();
        if (empty($allc)) {
          echo '<div style="color:var(--muted)">No coupons available.</div>';
        } else {
          foreach($allc as $cc) {
            echo '<div style="padding:6px 0;border-bottom:1px dashed #f0f2f5">';
            echo '<div style="font-weight:700">'.htmlspecialchars($cc['code']).' <span style="font-weight:600;color:var(--muted);font-size:13px">('.($cc['discount_type']=='percent' ? $cc['discount_value'].'%' : number_format($cc['discount_value'],2)).')</span></div>';
            echo '<div style="color:var(--muted);font-size:13px">Valid: '.htmlspecialchars($cc['valid_from'] ?? '—').' — '.htmlspecialchars($cc['valid_to'] ?? '—').'</div>';
            echo '</div>';
          }
        }
      ?>
    </div>
  </div>
</div>

<h3 style="margin-top:16px">Add Purchase</h3>
<div style="background:#fff;padding:12px;border-radius:8px;border:1px solid var(--border)">
  <form method="post">
    <input type="hidden" name="add_purchase" value="1">
    <div class="form-row">
      <label>Vehicle (optional)</label>
      <select name="vehicle_id">
        <option value="">-- select vehicle --</option>
        <?php foreach($vehicles as $v): ?>
          <option value="<?=$v['id']?>"><?=htmlspecialchars($v['brand'].' '.$v['model'].' — '.number_format($v['price'],2))?></option>
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
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
