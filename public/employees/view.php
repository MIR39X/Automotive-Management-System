<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header('Location: list.php');
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM employee WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();
if (!$employee) {
  echo '<div class="card">Employee not found</div>';
  require_once __DIR__ . '/../../includes/footer.php';
  exit;
}

$employeeName = trim($employee['name'] ?? '');
$employeeConfirm = $employeeName !== ''
  ? "Remove {$employeeName}? Their profile and history will be deleted."
  : "Delete this employee? Their profile and history will be deleted.";

function formatValue(?string $value, string $fallback = 'Not provided'): string {
  if ($value === null) return $fallback;
  $trimmed = trim((string)$value);
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
  return number_format((float)$value, 2);
}
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
    font-size:28px;
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
    box-shadow:0 10px 30px rgba(15,23,42,0.05);
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
  .profile-sections {
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
  .danger {
    border-color:#fecdd3;
    background:#fff1f2;
  }
  .danger .label { color:#be123c; }
  .danger .value { color:#9f1239; }
  .action-delete {
    color:#dc2626;
    text-decoration:none;
    font-weight:500;
  }
</style>

<div class="profile-hero">
  <div>
    <p class="profile-eyebrow">Employee Profile</p>
    <h2><?=htmlspecialchars($employee['name'])?></h2>
    <p class="profile-subtitle">
      <?=formatValue($employee['role'], 'Role not set')?> &middot;
      Hired <?=formatDateValue($employee['hire_date'])?>
    </p>
  </div>
<div class="profile-actions">
    <a class="btn" style="background-color:#2563eb;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none" href="edit.php?id=<?=$employee['id']?>">Edit profile</a>
    <a class="btn" style="background-color:#2563eb;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none" href="list.php">Back to employees</a>
  </div>
</div>

<div class="profile-summary">
  <div class="summary-card">
    <span class="label">Employee ID</span>
    <span class="value">#<?=htmlspecialchars($employee['id'])?></span>
    <span class="hint">Internal reference</span>
  </div>
  <div class="summary-card">
    <span class="label">Salary</span>
    <span class="value"><?=formatCurrencyValue($employee['salary'])?></span>
    <span class="hint">per pay period</span>
  </div>
  <div class="summary-card danger">
    <span class="label">CNIC</span>
    <span class="value"><?=formatValue($employee['cnic'])?></span>
    <span class="hint">Sensitive information</span>
  </div>
</div>

<div class="profile-sections">
  <section class="profile-card detail">
    <h3>Personal Details</h3>
    <dl>
      <dt>Date of Birth</dt>
      <dd><?=formatDateValue($employee['dob'])?></dd>
      <dt>National ID</dt>
      <dd><?=formatValue($employee['cnic'])?></dd>
    </dl>
  </section>

  <section class="profile-card detail">
    <h3>Employment</h3>
    <dl>
      <dt>Role</dt>
      <dd><?=formatValue($employee['role'], 'Not assigned')?></dd>
      <dt>Phone</dt>
      <dd><?=formatValue($employee['phone'])?></dd>
      <dt>Hire Date</dt>
      <dd><?=formatDateValue($employee['hire_date'])?></dd>
      <dt>Salary</dt>
      <dd><?=formatCurrencyValue($employee['salary'])?></dd>
    </dl>
  </section>
</div>

<div style="margin-top:18px">
  <a
    class="action-delete"
    href="delete.php?id=<?=$employee['id']?>"
    data-confirm="<?=htmlspecialchars($employeeConfirm, ENT_QUOTES)?>"
    data-confirm-title="Delete employee"
    data-confirm-cta="Delete"
    data-confirm-style="danger"
  >
    Delete employee
  </a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>



