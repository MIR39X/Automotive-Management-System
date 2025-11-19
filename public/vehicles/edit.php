<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$maxFileSize = 5 * 1024 * 1024; // 5MB
$uploadDir = __DIR__ . '/../../assets/uploads/';

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

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $vin   = trim($_POST['vin'] ?? null) ?: null;
  $brand = trim($_POST['brand'] ?? '');
  $model = trim($_POST['model'] ?? '');
  $year  = intval($_POST['year'] ?? 0) ?: null;
  $price = floatval($_POST['price'] ?? 0);
  $status = $_POST['status'] ?? 'available';
  $description = trim($_POST['description'] ?? '');
  $newImageName = null;

  if ($brand === '') $errors[] = 'Brand is required';
  if ($model === '') $errors[] = 'Model is required';

  // Handle new upload
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
        if ($mime === 'image/jpeg') $ext = '.jpg';
        elseif ($mime === 'image/png') $ext = '.png';
        elseif ($mime === 'image/gif') $ext = '.gif';
        else $errors[] = 'Only JPG, PNG and GIF allowed.';

        if ($ext) {
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
    // If new image uploaded, delete old file
    if ($newImageName) {
      if (!empty($row['image'])) {
        $oldPath = $uploadDir . $row['image'];
        if (file_exists($oldPath)) @unlink($oldPath);
      }
      $imageToStore = $newImageName;
    } else {
      $imageToStore = $row['image'];
    }

    $stmt = $pdo->prepare("UPDATE vehicle SET vin = ?, brand = ?, model = ?, year = ?, price = ?, status = ?, description = ?, image = ? WHERE id = ?");
    $stmt->execute([$vin, $brand, $model, $year, $price, $status, $description, $imageToStore, $id]);
    header('Location: list.php');
    exit;
  }
} else {
  $vin = $row['vin'];
  $brand = $row['brand'];
  $model = $row['model'];
  $year = $row['year'];
  $price = $row['price'];
  $status = $row['status'];
  $description = $row['description'];
  $currentImage = $row['image'];
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
    margin-bottom:24px;
  }
  .vehicle-hero h2 { margin:0;font-size:30px;color:#0f172a; }
  .vehicle-hero p { margin:6px 0 0;color:#475569; }
  .vehicle-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .vehicle-layout {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
  }
  .vehicle-card {
    border-radius:16px;
    border:1px solid #e2e8f0;
    background:#fff;
    padding:24px;
    box-shadow:0 12px 40px rgba(15,23,42,0.05);
  }
  .vehicle-card h3 { margin-top:0;color:#0f172a; }
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

<div class="vehicle-hero">
  <div>
    <h2>Edit Vehicle</h2>
    <p>Update specs, pricing, and imagery for this unit.</p>
  </div>
  <a class="btn" href="list.php">Back to vehicles</a>
</div>

<?php if (!empty($errors)): ?>
  <div class="error-box">
    <?php foreach ($errors as $e): ?><?=htmlspecialchars($e) . '<br>'?><?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" novalidate>
  <div class="vehicle-layout">
    <section class="vehicle-card">
      <h3>Vehicle Details</h3>
      <div class="form-grid">
        <div class="form-group">
          <label>VIN</label>
          <input type="text" name="vin" value="<?=htmlspecialchars($vin ?? '')?>">
        </div>
        <div class="form-group">
          <label>Brand*</label>
          <input type="text" name="brand" required value="<?=htmlspecialchars($brand ?? '')?>">
        </div>
        <div class="form-group">
          <label>Model*</label>
          <input type="text" name="model" required value="<?=htmlspecialchars($model ?? '')?>">
        </div>
        <div class="form-group">
          <label>Year</label>
          <input type="number" name="year" min="1900" max="2099" value="<?=htmlspecialchars($year ?? '')?>">
        </div>
        <div class="form-group">
          <label>Price</label>
          <input type="number" name="price" step="0.01" value="<?=htmlspecialchars($price ?? '')?>">
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="available" <?=($status === 'available') ? 'selected' : ''?>>available</option>
            <option value="sold" <?=($status === 'sold') ? 'selected' : ''?>>sold</option>
            <option value="service" <?=($status === 'service') ? 'selected' : ''?>>service</option>
          </select>
        </div>
      </div>

      <div class="form-group" style="margin-top:16px;">
        <label>Description</label>
        <textarea name="description"><?=htmlspecialchars($description ?? '')?></textarea>
      </div>
    </section>

    <section class="vehicle-card">
      <h3>Gallery</h3>
      <div>
        <span style="font-size:13px;text-transform:uppercase;color:#94a3b8;letter-spacing:0.08em;">Current Image</span>
        <div class="preview-box" style="margin-top:10px;">
          <?php if (!empty($row['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $row['image'])): ?>
            <img id="current-image" src="/ams_project/assets/uploads/<?=htmlspecialchars($row['image'])?>" alt="">
          <?php else: ?>
            <div style="color:#94a3b8;font-size:14px" id="current-image">No image uploaded</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="upload-area">
        <strong>Replace image (optional)</strong>
        <p style="margin:8px 0 0;">JPEG, PNG or GIF up to 5MB.</p>
        <input type="file" name="image" accept="image/*" id="vehicle-image-input">
      </div>
    </section>
  </div>

  <div class="form-actions">
    <button class="btn" type="submit">Update vehicle</button>
    <a href="list.php">Cancel</a>
  </div>
</form>

<script>
  const fileInput = document.getElementById('vehicle-image-input');
  const previewBox = document.querySelector('.preview-box');
  const currentImage = document.getElementById('current-image');
  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          if (currentImage && currentImage.tagName === 'IMG') {
            currentImage.src = e.target.result;
          } else if (currentImage) {
            const img = document.createElement('img');
            img.src = e.target.result;
            previewBox.innerHTML = '';
            previewBox.appendChild(img);
          } else {
            const img = document.createElement('img');
            img.src = e.target.result;
            previewBox.innerHTML = '';
            previewBox.appendChild(img);
          }
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>




