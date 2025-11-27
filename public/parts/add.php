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

$errors = [];
$maxFileSize = 5 * 1024 * 1024; // 5 MB
$uploadDir = __DIR__ . '/../../assets/uploads/';

// suppliers dropdown (assume table 'supplier' with column 'name')
$suppliers = $pdo->query("SELECT id, name FROM supplier ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// form vars
$name = $brand_company = $compatible_makes = $has_warranty = $description = '';
$buying_price = $mrp = $supplier_id = $quantity = null;
$imageName = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name             = trim($_POST['name'] ?? '');
  $brand_company    = trim($_POST['brand_company'] ?? '');
  $compatible_makes = trim($_POST['compatible_makes'] ?? '');
  $has_warranty     = $_POST['has_warranty'] ?? 'no'; // 'yes' / 'no'
  $buying_price     = $_POST['buying_price'] !== '' ? (float)$_POST['buying_price'] : null;
  $mrp              = $_POST['mrp'] !== '' ? (float)$_POST['mrp'] : null;
  $supplier_id      = $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;
  $quantity         = $_POST['quantity'] !== '' ? (int)$_POST['quantity'] : null;
  $description      = trim($_POST['description'] ?? '');

  // basic validation
  if ($name === '')           $errors[] = 'Part name is required';
  if ($brand_company === '')  $errors[] = 'Brand / company is required';
  if ($quantity === null || $quantity < 0)       $errors[] = 'Quantity must be 0 or greater';
  if ($buying_price === null || $buying_price < 0) $errors[] = 'Buying price must be 0 or greater';
  if ($mrp === null || $mrp < 0)                 $errors[] = 'MRP must be 0 or greater';
  if ($supplier_id === null || $supplier_id <= 0) $errors[] = 'Supplier is required';

  // image upload (optional)
  if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['image'];

    if ($f['error'] !== UPLOAD_ERR_OK) {
      $errors[] = 'Error uploading image.';
    } else if ($f['size'] > $maxFileSize) {
      $errors[] = 'Image is too large (max 5MB).';
    } else {
      $info = @getimagesize($f['tmp_name']);
      if ($info === false) {
        $errors[] = 'Uploaded file is not a valid image.';
      } else {
        $mime = $info['mime'];
        $ext = null;
        if ($mime === 'image/jpeg')      $ext = '.jpg';
        elseif ($mime === 'image/png')   $ext = '.png';
        elseif ($mime === 'image/gif')   $ext = '.gif';
        else $errors[] = 'Only JPG, PNG and GIF allowed.';

        if ($ext && empty($errors)) {
          $imageName = time() . '_' . uniqid() . $ext;
          $dest = $uploadDir . $imageName;
          if (!move_uploaded_file($f['tmp_name'], $dest)) {
            $errors[] = 'Failed to move uploaded file.';
          }
        }
      }
    }
  }

  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare("
        INSERT INTO part
          (name, brand_company, compatible_makes, has_warranty, buying_price, mrp, supplier_id, quantity, description, image)
        VALUES
          (?,    ?,             ?,                 ?,            ?,            ?,   ?,          ?,         ?,           ?)
      ");

      $stmt->execute([
        $name,
        $brand_company,
        $compatible_makes,
        $has_warranty,     // store as 'yes' / 'no' (make sure DB enum matches)
        $buying_price,
        $mrp,
        $supplier_id,
        $quantity,
        $description,
        $imageName
      ]);

      header('Location: list.php');
      exit;

    } catch (PDOException $e) {
      // Debug ke liye uncomment karo:
      // $errors[] = "DB ERROR: " . $e->getMessage();
      $errors[] = "An unexpected error occurred while saving the part.";
    }
  }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
  .part-hero {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    padding:24px;
    border-radius:18px;
    background:linear-gradient(135deg,#eff6ff,#ede9fe);
    border:1px solid #dbeafe;
    gap:24px;
    flex-wrap:wrap;
    margin-bottom:24px;
  }
  .part-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .part-hero p { margin:6px 0 0;color:#475569; }
  .part-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .part-form-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
  }
  .part-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .part-card h3 { margin-top:0;color:#0f172a; }
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
  .form-group input,
  .form-group select,
  .form-group textarea {
    padding:12px;
    border-radius:10px;
    border:1px solid #e2e8f0;
    font-size:15px;
    color:#0f172a;
  }
  .form-group textarea {
    min-height:90px;
    resize:vertical;
  }
  .form-actions {
    margin-top:24px;
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
  .upload-area {
    border:1px dashed #cbd5f5;
    border-radius:16px;
    padding:24px;
    text-align:center;
    color:#64748b;
  }
  .upload-area strong { color:#2563eb; }
  .upload-area input { margin-top:10px; }
  .preview-box {
    margin-top:18px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#f8fafc;
    min-height:200px;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
  }
  .preview-box img { width:100%;height:100%;object-fit:cover; }
  .error-box {
    color:#b91c1c;
    margin-bottom:14px;
    padding:12px;
    border:1px solid #fecdd3;
    border-radius:10px;
    background:#fff1f2;
  }
</style>

<div class="part-hero">
  <div>
    <h2>Add Part</h2>
    <p>Capture each part with supplier, pricing and compatibility details.</p>
  </div>
  <a class="btn" href="list.php">Back to parts</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?>
      <?= htmlspecialchars($e) . '<br>' ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" novalidate>
  <div class="part-form-grid">
    <section class="part-card">
      <h3>Part Information</h3>
      <div class="form-grid">
        <div class="form-group">
          <label>Part Name*</label>
          <input type="text" name="name" required value="<?= htmlspecialchars($name) ?>">
        </div>

        <div class="form-group">
          <label>Brand / Company*</label>
          <input type="text" name="brand_company" required value="<?= htmlspecialchars($brand_company) ?>">
        </div>

        <div class="form-group">
          <label>Compatible Makes</label>
          <input
            type="text"
            name="compatible_makes"
            placeholder="e.g. Toyota, Honda, Suzuki"
            value="<?= htmlspecialchars($compatible_makes) ?>"
          >
        </div>

        <div class="form-group">
          <label>Warranty</label>
          <select name="has_warranty">
            <option value="no"  <?= $has_warranty === 'no'  ? 'selected' : '' ?>>No</option>
            <option value="yes" <?= $has_warranty === 'yes' ? 'selected' : '' ?>>Yes</option>
          </select>
        </div>

        <div class="form-group">
          <label>Buying Price*</label>
          <input type="number" step="0.01" name="buying_price" value="<?= htmlspecialchars($buying_price ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>MRP / Selling Price*</label>
          <input type="number" step="0.01" name="mrp" value="<?= htmlspecialchars($mrp ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Supplier*</label>
          <select name="supplier_id" required>
            <option value="">-- Select supplier --</option>
            <?php foreach ($suppliers as $s): ?>
              <option value="<?= $s['id'] ?>" <?= ($supplier_id == $s['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Quantity in stock*</label>
          <input type="number" name="quantity" min="0" value="<?= htmlspecialchars($quantity ?? 0) ?>" required>
        </div>
      </div>

      <div class="form-group" style="margin-top:16px;">
        <label>Description</label>
        <textarea name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
      </div>
    </section>

    <section class="part-card">
      <h3>Part Image</h3>
      <p style="color:#64748b">JPEG, PNG or GIF up to 5MB.</p>
      <div class="upload-area">
        <strong>Drag and drop or choose a file</strong>
        <input type="file" name="image" accept="image/*" id="part-image-input">
      </div>
      <div class="preview-box" id="image-preview">No image selected</div>
    </section>
  </div>

  <div class="form-actions">
    <button class="btn" type="submit">Save part</button>
    <a href="list.php">Cancel</a>
  </div>
</form>

<script>
  const fileInput = document.getElementById('part-image-input');
  const previewBox = document.getElementById('image-preview');
  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          previewBox.innerHTML = '';
          previewBox.appendChild(img);
        };
        reader.readAsDataURL(this.files[0]);
      } else {
        previewBox.textContent = 'No image selected';
      }
    });
  }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
