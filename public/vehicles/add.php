<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$errors = [];
$maxFileSize = 5 * 1024 * 1024; // 5 MB
$uploadDir = __DIR__ . '/../../assets/uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $vin   = trim($_POST['vin'] ?? null) ?: null;
  $brand = trim($_POST['brand'] ?? '');
  $model = trim($_POST['model'] ?? '');
  $year  = intval($_POST['year'] ?? 0) ?: null;
  $price = floatval($_POST['price'] ?? 0);
  $description = trim($_POST['description'] ?? '');
  $imageName = null;

  if ($brand === '') $errors[] = 'Brand is required';
  if ($model === '') $errors[] = 'Model is required';

  // Image upload (same as before)
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
        if ($mime === 'image/jpeg') $ext = '.jpg';
        elseif ($mime === 'image/png') $ext = '.png';
        elseif ($mime === 'image/gif') $ext = '.gif';
        else $errors[] = 'Only JPG, PNG and GIF allowed.';

        if ($ext) {
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
      $stmt = $pdo->prepare("INSERT INTO vehicle (vin, brand, model, year, price, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([$vin, $brand, $model, $year, $price, $description, $imageName]);

      header('Location: list.php');
      exit;

    } catch (PDOException $e) {
      // Duplicate VIN error code 1062
      if ($e->getCode() == 23000) {
        $errors[] = "A vehicle with this VIN already exists. Please use a unique VIN.";
      } else {
        $errors[] = "An unexpected error occurred while saving the vehicle.";
      }
    }
  }
}
?>


<div class="card">
  <?php if (!empty($errors)): ?>
    <div style="color:#b91c1c;margin-bottom:10px">
      <?php foreach ($errors as $e): ?><?=htmlspecialchars($e) . '<br>'?><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" novalidate>
    <div class="form-row">
      <label>VIN</label>
      <input type="text" name="vin" value="<?=isset($vin) ? htmlspecialchars($vin) : ''?>">
    </div>

    <div class="form-row">
      <label>Brand *</label>
      <input type="text" name="brand" required value="<?=isset($brand) ? htmlspecialchars($brand) : ''?>">
    </div>

    <div class="form-row">
      <label>Model *</label>
      <input type="text" name="model" required value="<?=isset($model) ? htmlspecialchars($model) : ''?>">
    </div>

    <div class="form-row">
      <label>Year</label>
      <input type="number" name="year" min="1900" max="2099" value="<?=isset($year) ? htmlspecialchars($year) : ''?>">
    </div>

    <div class="form-row">
      <label>Price</label>
      <input type="number" name="price" step="0.01" value="<?=isset($price) ? htmlspecialchars($price) : ''?>">
    </div>

    <div class="form-row">
      <label>Description</label>
      <textarea name="description" rows="4" style="width:100%"><?=isset($description) ? htmlspecialchars($description) : ''?></textarea>
    </div>

    <div class="form-row">
      <label>Image (JPG, PNG, GIF) — optional</label>
      <input type="file" name="image" accept="image/*">
    </div>

    <div style="margin-top:12px">
      <button class="btn" type="submit">Save vehicle</button>
      <a href="list.php" style="margin-left:10px;color:#6b7280;text-decoration:none">Cancel</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
