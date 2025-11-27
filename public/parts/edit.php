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
$maxFileSize = 5 * 1024 * 1024; // 5MB
$uploadDir = __DIR__ . '/../../assets/uploads/';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

// fetch part
$stmt = $pdo->prepare("SELECT * FROM part WHERE id = ?");
$stmt->execute([$id]);
$part = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$part) {
  header('Location: list.php');
  exit;
}

// initialize form vars
$part_name       = $part['part_name']       ?? '';
$company         = $part['company']         ?? '';
$compatible_cars = $part['compatible_cars'] ?? '';
$has_warranty    = (string)($part['has_warranty'] ?? '0');
$buying_price    = $part['buying_price']    ?? '';
$mrp             = $part['mrp']             ?? '';
$supplier_name   = $part['supplier_name']   ?? '';
$quantity        = $part['quantity']        ?? '';
$description     = $part['description']     ?? '';
$currentImage    = $part['image']           ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // get new values from form
  $part_name       = trim($_POST['part_name']       ?? '');
  $company         = trim($_POST['company']         ?? '');
  $compatible_cars = trim($_POST['compatible_cars'] ?? '');
  $has_warranty    = isset($_POST['has_warranty']) ? (string)$_POST['has_warranty'] : '0';
  $buying_price    = trim($_POST['buying_price']    ?? '');
  $mrp             = trim($_POST['mrp']             ?? '');
  $supplier_name   = trim($_POST['supplier_name']   ?? '');
  $quantity        = trim($_POST['quantity']        ?? '');
  $description     = trim($_POST['description']     ?? '');
  $newImageName    = null;

  // validation
  if ($part_name === '')    $errors[] = 'Part name is required';
  if ($company === '')      $errors[] = 'Part company is required';
  if ($buying_price === '' || !is_numeric($buying_price)) $errors[] = 'Buying price must be a number';
  if ($mrp === '' || !is_numeric($mrp))                   $errors[] = 'MRP must be a number';
  if ($quantity === '' || !ctype_digit($quantity))        $errors[] = 'Quantity must be a whole number';

  // handle new upload
  if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['image'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
      $errors[] = 'Error uploading image.';
    } else if ($f['size'] > $maxFileSize) {
      $errors[] = 'Image too large (max 5MB).';
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

        if ($ext) {
          if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
          }
          $newImageName = time() . '_' . uniqid() . $ext;
          $dest = $uploadDir . $newImageName;
          if (!move_uploaded_file($f['tmp_name'], $dest)) {
            $errors[] = 'Failed to move uploaded file.';
          }
        }
      }
    }
  }

  if (empty($errors)) {
    // decide which image to store
    if ($newImageName) {
      // delete old image if exists
      if (!empty($currentImage)) {
        $oldPath = $uploadDir . $currentImage;
        if (file_exists($oldPath)) @unlink($oldPath);
      }
      $imageToStore = $newImageName;
    } else {
      $imageToStore = $currentImage;
    }

    $stmt = $pdo->prepare("
      UPDATE part
      SET part_name = ?,
          company = ?,
          compatible_cars = ?,
          has_warranty = ?,
          buying_price = ?,
          mrp = ?,
          supplier_name = ?,
          quantity = ?,
          description = ?,
          image = ?
      WHERE id = ?
    ");
    $stmt->execute([
      $part_name,
      $company,
      $compatible_cars,
      (int)$has_warranty,
      (float)$buying_price,
      (float)$mrp,
      $supplier_name,
      (int)$quantity,
      $description,
      $imageToStore,
      $id
    ]);

    header('Location: list.php');
    exit;
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
  .part-layout {
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
    min-height:120px;
    resize:vertical;
  }
  .upload-area {
    border:1px dashed #cbd5f5;
    border-radius:16px;
    padding:24px;
    text-align:center;
    color:#64748b;
    margin-top:12px;
  }
  .upload-area strong { color:#2563eb; }
  .upload-area input { margin-top:10px; }
  .preview-box {
    margin-top:18px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#f8fafc;
    min-height:220px;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
  }
  .preview-box img {
    width:100%;
    height:100%;
    object-fit:cover;
  }
  .error-box {
    color:#b91c1c;
    margin-bottom:14px;
    padding:12px;
    border:1px solid #fecdd3;
    border-radius:10px;
    background:#fff1f2;
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
</style>

<div class="part-hero">
  <div>
    <h2>Edit Part</h2>
    <p>Update specs, pricing, compatibility and imagery for this part.</p>
  </div>
  <a class="btn" href="list.php">Back to parts</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?><?= htmlspecialchars($e) . '<br>' ?><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" novalidate>
  <div class="part-layout">
    <section class="part-card">
      <h3>Part Details</h3>
      <div class="form-grid">
        <div class="form-group">
          <label>Part Name*</label>
          <input type="text" name="part_name" required value="<?= htmlspecialchars($part_name) ?>">
        </div>
        <div class="form-group">
          <label>Part Company*</label>
          <input type="text" name="company" required value="<?= htmlspecialchars($company) ?>">
        </div>
        <div class="form-group">
          <label>Compatible Makes</label>
          <input type="text" name="compatible_cars" placeholder="Toyota,Honda,Suzuki" value="<?= htmlspecialchars($compatible_cars) ?>">
        </div>
        <div class="form-group">
          <label>Has Warranty?</label>
          <select name="has_warranty">
            <option value="0" <?= $has_warranty === '0' ? 'selected' : '' ?>>No</option>
            <option value="1" <?= $has_warranty === '1' ? 'selected' : '' ?>>Yes</option>
          </select>
        </div>
        <div class="form-group">
          <label>Buying Price*</label>
          <input type="number" step="0.01" name="buying_price" value="<?= htmlspecialchars($buying_price) ?>">
        </div>
        <div class="form-group">
          <label>MRP*</label>
          <input type="number" step="0.01" name="mrp" value="<?= htmlspecialchars($mrp) ?>">
        </div>
        <div class="form-group">
          <label>Supplier Name</label>
          <input type="text" name="supplier_name" value="<?= htmlspecialchars($supplier_name) ?>">
        </div>
        <div class="form-group">
          <label>Quantity*</label>
          <input type="number" name="quantity" min="0" value="<?= htmlspecialchars($quantity) ?>">
        </div>
      </div>

      <div class="form-group" style="margin-top:16px;">
        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($description) ?></textarea>
      </div>
    </section>

    <section class="part-card">
      <h3>Image</h3>
      <div>
        <span style="font-size:13px;text-transform:uppercase;color:#94a3b8;letter-spacing:0.08em;">Current Image</span>
        <div class="preview-box" style="margin-top:10px;">
          <?php if (!empty($currentImage) && file_exists($uploadDir . $currentImage)): ?>
            <img id="current-image" src="<?= $base ?>/assets/uploads/<?= htmlspecialchars($currentImage) ?>" alt="">
          <?php else: ?>
            <div style="color:#94a3b8;font-size:14px" id="current-image">No image uploaded</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="upload-area">
        <strong>Replace image (optional)</strong>
        <p style="margin:8px 0 0;">JPEG, PNG or GIF up to 5MB.</p>
        <input type="file" name="image" accept="image/*" id="part-image-input">
      </div>
    </section>
  </div>

  <div class="form-actions">
    <button class="btn" type="submit">Update Part</button>
    <a href="list.php">Cancel</a>
  </div>
</form>

<script>
  const fileInput = document.getElementById('part-image-input');
  const previewBox = document.querySelector('.preview-box');
  const currentImageEl = document.getElementById('current-image');
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
      }
    });
  }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
