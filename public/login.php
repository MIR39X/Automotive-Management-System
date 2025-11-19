<?php
$base = '/ams_project';
session_start();

$redirectParam = $_GET['redirect'] ?? '';
$redirectTarget = ($redirectParam && strpos($redirectParam, '/') === 0) ? $redirectParam : $base . '/public/index.php';

if (isset($_SESSION['user'])) {
  header("Location: $redirectTarget");
  exit;
}

require_once __DIR__ . '/../includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $redirectInput = $_POST['redirect'] ?? '';
  $redirectAfterLogin = ($redirectInput && strpos($redirectInput, '/') === 0) ? $redirectInput : $redirectTarget;

  $demoUser = [
    'username' => 'admin',
    'password' => 'admin123',
  ];

  if ($username === $demoUser['username'] && $password === $demoUser['password']) {
    $_SESSION['user'] = [
      'username' => $username,
      'logged_in_at' => date('Y-m-d H:i:s'),
    ];
    header("Location: $redirectAfterLogin");
    exit;
  } else {
    $errors[] = 'Invalid username or password.';
  }
}
?>

<style>
  body {
    background:linear-gradient(180deg,#f1f5f9 0%,#e2e8f0 100%);
  }
  .auth-wrapper {
    min-height:70vh;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .auth-card {
    width:100%;
    max-width:460px;
    background:#fff;
    border-radius:28px;
    padding:32px;
    box-shadow:0 25px 70px rgba(15,23,42,0.15);
    border:1px solid rgba(226,232,240,0.8);
  }
  .auth-card h1 {
    margin:0;
    font-size:30px;
    color:#0f172a;
  }
  .auth-card p {
    margin:8px 0 24px;
    color:#64748b;
  }
  .form-group {
    margin-bottom:18px;
  }
  .form-group label {
    display:block;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:0.08em;
    color:#94a3b8;
    margin-bottom:6px;
  }
  .form-group input {
    width:100%;
    padding:14px;
    border-radius:14px;
    border:1px solid #e2e8f0;
    font-size:16px;
    color:#0f172a;
    background:#f8fafc;
  }
  .auth-actions {
    margin-top:10px;
  }
  .auth-actions button {
    width:100%;
    padding:14px;
    border:none;
    border-radius:14px;
    background:linear-gradient(135deg,#2563eb,#7c3aed);
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    box-shadow:0 15px 35px rgba(37,99,235,0.35);
  }
  .error-box {
    background:#fee2e2;
    color:#b91c1c;
    border:1px solid #fecaca;
    border-radius:14px;
    padding:12px 16px;
    margin-bottom:18px;
  }
</style>

<div class="auth-wrapper">
  <div class="auth-card">
    <h1>Sign in to AMS</h1>
    <p>Use your demo credentials to continue. Default: <code>admin / admin123</code></p>

    <?php if (!empty($errors)): ?>
      <div class="error-box">
        <?php foreach ($errors as $error): ?>
          <?= htmlspecialchars($error) ?><br>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectParam) ?>">
      <div class="form-group">
        <label for="username">Username</label>
        <input id="username" type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>
      </div>
      <div class="auth-actions">
        <button type="submit">Sign in</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
