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

// CUSTOMERS DROPDOWN
$customers = $pdo->query("SELECT id, name FROM customer ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// PARTS DROPDOWN (part table: id, name, brand_company, mrp, quantity)
$parts = $pdo->query("
  SELECT id, name, brand_company, mrp AS price, quantity
  FROM part
  ORDER BY brand_company, name
")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$customer_id  = null;
$part_id      = null;
$sale_date    = date('Y-m-d');
$qty          = 1;
$unit_price   = '';
$total_amount = 0.0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $customer_id = intval($_POST['customer_id'] ?? 0);
  $part_id     = intval($_POST['part_id'] ?? 0);
  $sale_date   = $_POST['sale_date'] ?? '';
  $qty         = intval($_POST['qty'] ?? 0);
  $unit_price  = $_POST['unit_price'] ?? '';

  if ($customer_id <= 0) $errors[] = 'Customer is required';
  if ($part_id     <= 0) $errors[] = 'Part is required';
  if ($sale_date === '') $errors[] = 'Sale date is required';
  if ($qty <= 0)         $errors[] = 'Quantity must be greater than 0';

  if ($unit_price === '' || !is_numeric($unit_price)) {
    $errors[] = 'Unit price must be a valid number';
  }

  // --- Integrity: stock check ---
  $partRow = null;
  if ($part_id > 0) {
    $stmt = $pdo->prepare("SELECT quantity FROM part WHERE id = ?");
    $stmt->execute([$part_id]);
    $partRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$partRow) {
      $errors[] = 'Selected part does not exist in inventory.';
    } else {
      $availableQty = (int)$partRow['quantity'];
      if ($qty > $availableQty) {
        $errors[] = "Not enough stock. Available quantity: {$availableQty}.";
      }
    }
  }

  if (empty($errors)) {
    $unit = (float)$unit_price;
    $lineTotal = $unit * $qty;
    $total_amount = $lineTotal;

    try {
      // transaction start -> ya to sab hoga, ya kuch bhi nahi (atomicity + integrity)
      $pdo->beginTransaction();

      // 1) Insert into retailsale
      $stmt = $pdo->prepare("
        INSERT INTO retailsale (customer_id, sale_date, total_amount)
        VALUES (?, ?, ?)
      ");
      $stmt->execute([$customer_id, $sale_date, $total_amount]);
      $retailId = $pdo->lastInsertId();

      // 2) Insert into retailsale_details
      $stmt = $pdo->prepare("
        INSERT INTO retailsale_details (retailsale_id, part_id, quantity, unit_price, line_total)
        VALUES (?, ?, ?, ?, ?)
      ");
      $stmt->execute([$retailId, $part_id, $qty, $unit, $lineTotal]);

      // 3) Update part stock (quantity = quantity - qty)
      $stmt = $pdo->prepare("
        UPDATE part
        SET quantity = quantity - ?
        WHERE id = ?
      ");
      $stmt->execute([$qty, $part_id]);

      $pdo->commit();

      header('Location: list.php');
      exit;

    } catch (PDOException $e) {
      $pdo->rollBack();
      // Debug ke liye agar dekhna ho:
      // $errors[] = "DB ERROR: " . $e->getMessage();
      $errors[] = "An error occurred while saving the retail sale.";
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
  .retail-hero {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    padding:24px;
    border-radius:18px;
    background:linear-gradient(135deg,#ecfeff,#fef3c7);
    border:1px solid #bae6fd;
    gap:24px;
    flex-wrap:wrap;
    margin-bottom:24px;
  }
  .retail-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .retail-hero p { margin:6px 0 0;color:#475569; }
  .retail-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .retail-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .form-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
  }
  .form-group { display:flex;flex-direction:column; }
  .form-group label {
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:#94a3b8;
    margin-bottom:6px;
  }
  .form-group select,
  .form-group input {
    padding:10px;
    border-radius:10px;
    border:1px solid #e2e8f0;
    font-size:15px;
    color:#0f172a;
  }
  .summary-box {
    margin-top:18px;
    padding:12px 16px;
    border-radius:12px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    color:#0f172a;
    display:flex;
    justify-content:space-between;
    align-items:center;
  }
  .summary-box strong { font-size:16px; }
  .form-actions {
    margin-top:20px;
    display:flex;
    gap:12px;
    flex-wrap:wrap;
  }
  .form-actions .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 22px;
    border-radius:5px;
    border:none;
    cursor:pointer;
  }
  .form-actions a {
    padding:10px 22px;
    border-radius:5px;
    border:1px solid rgba(37,99,235,0.3);
    color:#2563eb;
    text-decoration:none;
  }
  .error-box {
    color:#b91c1c;
    margin-bottom:14px;
    padding:12px;
    border:1px solid #fecdd3;
    border-radius:10px;
    background:#fff1f2;
  }
</style>

<div class="retail-hero">
  <div>
    <h2>New Retail Sale</h2>
    <p>Record a walk-in parts sale for a customer.</p>
  </div>
  <a class="btn" href="list.php">Back to retail sales</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?>
      <?= htmlspecialchars($e) . '<br>' ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="retail-card">
  <form method="post" novalidate>
    <div class="form-grid">
      <div class="form-group">
        <label>Customer*</label>
        <select name="customer_id" required>
          <option value="">-- Select customer --</option>
          <?php foreach ($customers as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $customer_id == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Sale Date*</label>
        <input type="date" name="sale_date" value="<?= htmlspecialchars($sale_date) ?>" required>
      </div>
    </div>

    <h3 style="margin-top:20px;margin-bottom:8px;">Line Item</h3>
    <div class="form-grid">
      <div class="form-group">
        <label>Part*</label>
        <select name="part_id" id="part-select" required>
          <option value="">-- Select part --</option>
          <?php foreach ($parts as $p): ?>
            <option
              value="<?= $p['id'] ?>"
              data-price="<?= htmlspecialchars($p['price']) ?>"
              data-qty="<?= htmlspecialchars($p['quantity']) ?>"
              <?= $part_id == $p['id'] ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($p['brand_company'] . ' - ' . $p['name'] . ' (Stock: ' . $p['quantity'] . ')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Quantity*</label>
        <input type="number" name="qty" id="qty-input" min="1" value="<?= htmlspecialchars($qty) ?>" required>
      </div>

      <div class="form-group">
        <label>Unit Price (MRP)*</label>
        <input type="number" step="0.01" name="unit_price" id="price-input" value="<?= htmlspecialchars($unit_price) ?>" required>
      </div>
    </div>

    <div class="summary-box">
      <span>Line Total:</span>
      <strong id="line-total-display">$0.00</strong>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Save sale</button>
      <a href="list.php">Cancel</a>
    </div>
  </form>
</div>

<script>
  const partSelect = document.getElementById('part-select');
  const qtyInput = document.getElementById('qty-input');
  const priceInput = document.getElementById('price-input');
  const lineTotalDisplay = document.getElementById('line-total-display');

  function updateLineTotal() {
    const qty = parseFloat(qtyInput.value || '0');
    const price = parseFloat(priceInput.value || '0');
    const total = qty * price;
    lineTotalDisplay.textContent = '$' + total.toFixed(2);
  }

  if (partSelect) {
    partSelect.addEventListener('change', function () {
      const opt = this.options[this.selectedIndex];
      const priceAttr = opt.getAttribute('data-price');
      if (priceAttr !== null && priceAttr !== '') {
        priceInput.value = priceAttr;
        updateLineTotal();
      }
    });
  }

  if (qtyInput) qtyInput.addEventListener('input', updateLineTotal);
  if (priceInput) priceInput.addEventListener('input', updateLineTotal);

  // initial calculation
  updateLineTotal();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
