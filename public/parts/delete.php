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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // find image filename for this part
    $stmt = $pdo->prepare("SELECT image FROM part WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // delete image file if it exists
        if (!empty($row['image'])) {
            $path = __DIR__ . '/../../assets/uploads/' . $row['image'];
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        // delete part row
        $stmt = $pdo->prepare("DELETE FROM part WHERE id = ?");
        $stmt->execute([$id]);
    }

    header('Location: list.php');
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="card">
  <h2>Delete Part</h2>
  <p>No part selected. <a href="list.php">Back to list</a></p>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
