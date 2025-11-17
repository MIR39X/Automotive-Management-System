<?php
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
<div class="card" style="padding:20px;">
  <div style="display:flex;align-items:center;justify-content:space-between">
    <h2 style="margin:0;font-size:24px;color:#333">Edit Vehicle #<?=htmlspecialchars($id)?></h2>
    <div><a class="btn" href="list.php" style="background-color:#007BFF;color:#fff;padding:10px 15px;border-radius:5px;text-decoration:none">Back to vehicles</a></div>
  </div>
</div>

<div class="card" style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
  <?php if (!empty($errors)): ?>
    <div style="color:#b91c1c;margin-bottom:10px">
      <?php foreach ($errors as $e): ?><?=htmlspecialchars($e) . '<br>'?><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" novalidate>
    <div class="form-row" style="margin-bottom:15px">
      <label style="font-size:16px;color:#333">VIN</label>
      <input type="text" name="vin" value="<?=htmlspecialchars($vin)?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px">
    </div>

    <div class="form-row" style="margin-bottom:15px">
      <label style="font-size:16px;color:#333">Brand *</label>
      <input type="text" name="brand" required value="<?=htmlspecialchars($brand)?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px">
    </div>

    <div class="form-row" style="margin-bottom:15px">
      <label style="font-size:16px;color:#333">Model *</label>
      <input type="text" name="model" required value="<?=htmlspecialchars($model)?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px">
    </div>

    <div class="form-row" style="margin-bottom:15px">
      <label style="font-size:16px;color:#333">Year</label>
      <input type="number" name="year" min="1900" max="2099" value="<?=htmlspecialchars($year)?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px">
    </div>

    <div class="form-row" style="margin-bottom:15px">
      <label style="font-size:16px;color:#333">Price</label>
      <input type="number" name="price" step="0.01" value="<?=htmlspecialchars($price)?>" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px">
    </div>

    <div class="form-row" style="margin-bottom:15px">
      <label style="font-size:16px;color:#333">Status</label>
      <select name="status" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px">
        <option value="available" <?= ($status === 'available') ? 'selected' : '' ?>>available</option>
        <option value="sold" <?= ($status === 'sold') ? 'selected' : '' ?>>sold</option>
        <option value="service" <?= ($status === 'service') ? 'selected' : '' ?>>service</option>
      </select>
    </div>

    <div class="form-row" style="margin-bottom:15px">
      <label style="font-size:16px;color:#333">Description</label>
      <textarea name="description" rows="4" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:5px"><?=htmlspecialchars($description)?></textarea>
    </div>

    <div class="form-row" style="margin-bottom:15px">
      <label style="font-size:16px;color:#333">Current image</label>
      <?php if (!empty($row['image']) && file_exists(__DIR__ . '/../../assets/uploads/' . $row['image'])): ?>
        <div style="margin-bottom:8px">
          <img src="/ams_project/assets/uploads/<?=htmlspecialchars($row['image'])?>" alt="" style="max-width:180px;border:1px solid #e6e9ef;padding:6px;border-radius:6px">
        </div>
      <?php else: ?>
        <div style="color:#6b7280;margin-bottom:8px">No image uploaded</div>
      <?php endif; ?>
      <label style="font-size:16px;color:#333">Replace image (optional)</label>
      <div style="position:relative;display:inline-block;width:300px;">
        <input type="file" name="image" accept="image/*" id="file-upload" style="opacity:0;position:absolute;z-index:-1;width:100%;height:100%;">
        <label for="file-upload" style="display:block;width:100%;padding:8px;border:1px solid #ddd;border-radius:5px;background-color:#007BFF;color:#fff;text-align:center;cursor:pointer;font-size:14px">Choose File</label>
        <span id="file-name" style="display:block;margin-top:8px;color:#6c757d;font-size:12px">No file chosen</span>
      </div>
    </div>

    <div style="margin-top:20px;display:flex;gap:15px">
      <button class="btn" type="submit" style="background-color:#007BFF;color:#fff;padding:10px 15px;border-radius:5px;border:none">Update vehicle</button>
      <a href="list.php" style="background-color:#6c757d;color:#fff;padding:10px 15px;border-radius:5px;text-decoration:none">Cancel</a>
    </div>
  </form>
</div>

<script>
  document.getElementById('file-upload').addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
    document.getElementById('file-name').textContent = fileName;
  });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
