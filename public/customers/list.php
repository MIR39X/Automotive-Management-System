<?php
require_once __DIR__ . '/../../includes/db.php';
$requireAuth = true;
require_once __DIR__ . '/../../includes/header.php';

$rows = $pdo->query("SELECT * FROM customer ORDER BY created_at DESC")->fetchAll();
$totalCustomers = count($rows);
$withEmail = count(array_filter($rows, fn($c) => !empty(trim($c['email'] ?? ''))));
$withPhone = count(array_filter($rows, fn($c) => !empty(trim($c['phone'] ?? ''))));
$withNotes = count(array_filter($rows, fn($c) => !empty(trim($c['notes'] ?? ''))));

$monthStart = new DateTime('first day of this month 00:00:00');
$newThisMonth = count(array_filter($rows, function ($c) use ($monthStart) {
  if (empty($c['created_at'])) return false;
  $created = strtotime($c['created_at']);
  return $created !== false && $created >= $monthStart->getTimestamp();
}));

function initials(string $name): string {
  $trimmed = trim($name);
  if ($trimmed === '') return '?';
  $parts = preg_split('/\s+/', $trimmed);
  $first = strtoupper(substr($parts[0], 0, 1));
  $second = isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '';
  return $first . $second;
}

function formatDateValue(?string $value): string {
  if (!$value) return 'Not set';
  $timestamp = strtotime($value);
  return $timestamp ? date('M j, Y', $timestamp) : htmlspecialchars($value);
}
?>

<style>
  .customer-hero {
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
  .customer-hero h2 {
    margin:0;
    font-size:30px;
    color:#0f172a;
  }
  .customer-hero p {
    margin:6px 0 0;
    color:#475569;
  }
  .customer-hero .btn {
    background:#2563eb;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    border:none;
  }
  .customer-summary {
    margin-top:24px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
  }
  .customer-summary .card {
    border:1px solid #e2e8f0;
    border-radius:12px;
    padding:16px;
    background:#fff;
    box-shadow:0 8px 30px rgba(15,23,42,0.05);
  }
  .customer-summary .label {
    font-size:12px;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:0.08em;
  }
  .customer-summary .value {
    display:block;
    margin-top:6px;
    font-size:22px;
    font-weight:600;
    color:#0f172a;
  }
  .customer-table {
    margin-top:24px;
    border-radius:16px;
    border:1px solid #e2e8f0;
    overflow:hidden;
    box-shadow:0 10px 40px rgba(15,23,42,0.05);
  }
  .customer-table table {
    width:100%;
    border-collapse:collapse;
  }
  .customer-table thead {
    background:#f8fafc;
  }
  .customer-table th,
  .customer-table td {
    padding:14px 16px;
    text-align:left;
  }
  .customer-table tbody tr {
    border-top:1px solid #f1f5f9;
  }
  .customer-info {
    display:flex;
    align-items:center;
    gap:14px;
  }
  .customer-avatar {
    width:48px;
    height:48px;
    border-radius:14px;
    background:#e0e7ff;
    color:#3730a3;
    font-weight:700;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  .customer-name {
    font-weight:600;
    color:#0f172a;
  }
  .customer-meta {
    font-size:13px;
    color:#94a3b8;
  }
  .customer-actions a {
    margin-right:12px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
  }
  .customer-actions a.delete {
    color:#dc2626;
  }
  @media (max-width:768px) {
    .customer-table table,
    .customer-table thead,
    .customer-table tbody,
    .customer-table th,
    .customer-table td,
    .customer-table tr {
      display:block;
    }
    .customer-table tr {
      padding:12px 0;
    }
    .customer-table th {
      display:none;
    }
    .customer-table td {
      padding:6px 16px;
    }
    .customer-table td::before {
      content:attr(data-label);
      display:block;
      font-size:12px;
      text-transform:uppercase;
      color:#94a3b8;
      margin-bottom:4px;
      letter-spacing:0.08em;
    }
    .customer-actions a {
      display:inline-block;
      margin-bottom:6px;
    }
  }
</style>

<div class="customer-hero">
  <div>
    <h2>Customers</h2>
    <p><?=$totalCustomers?> customers in the CRM</p>
  </div>
  <a class="btn" href="add.php">+ Add customer</a>
</div>

<div class="customer-summary">
  <div class="card">
    <span class="label">Total Customers</span>
    <span class="value"><?=$totalCustomers?></span>
  </div>
  <div class="card">
    <span class="label">New This Month</span>
    <span class="value"><?=$newThisMonth?></span>
  </div>
  <div class="card">
    <span class="label">Email On File</span>
    <span class="value"><?=$withEmail?></span>
  </div>
  <div class="card">
    <span class="label">Notes Added</span>
    <span class="value"><?=$withNotes?></span>
  </div>
</div>

<div class="customer-table">
  <table role="table">
    <thead>
      <tr>
        <th>Customer</th>
        <th>Contact</th>
        <th>Created</th>
        <th>Notes</th>
        <th style="text-align:right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="5" style="text-align:center;color:#94a3b8;padding:20px">No customers yet.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <?php
            $notesPreview = trim($r['notes'] ?? '');
            $notesPreview = $notesPreview === '' ? 'No notes' : htmlspecialchars(mb_strimwidth($notesPreview, 0, 60, '...'));
            $contact = [];
            if (!empty($r['email'])) $contact[] = htmlspecialchars($r['email']);
            if (!empty($r['phone'])) $contact[] = htmlspecialchars($r['phone']);
            $contactInfo = empty($contact) ? '<span style="color:#cbd5f5">No contact info</span>' : implode(' &middot; ', $contact);
            $customerName = trim($r['name'] ?? '');
            $confirmCopy = $customerName !== ''
              ? "Remove {$customerName}? This will permanently delete their record."
              : "Delete this customer? This will permanently delete their record.";
          ?>
          <tr>
            <td data-label="Customer">
              <div class="customer-info">
                <div class="customer-avatar"><?=htmlspecialchars(initials($r['name'] ?? ''))?></div>
                <div>
                  <div class="customer-name">
                    <a href="view.php?id=<?=$r['id']?>" style="color:inherit;text-decoration:none"><?=htmlspecialchars($r['name'])?></a>
                  </div>
                  <div class="customer-meta">ID #<?=htmlspecialchars($r['id'])?></div>
                </div>
              </div>
            </td>
            <td data-label="Contact"><?=$contactInfo?></td>
            <td data-label="Created"><?=formatDateValue($r['created_at'] ?? null)?></td>
            <td data-label="Notes"><?=$notesPreview?></td>
            <td class="customer-actions" data-label="Actions" style="text-align:right;">
              <a href="view.php?id=<?=$r['id']?>">View</a>
              <a href="edit.php?id=<?=$r['id']?>">Edit</a>
              <a
                class="delete"
                href="delete.php?id=<?=$r['id']?>"
                data-confirm="<?=htmlspecialchars($confirmCopy, ENT_QUOTES)?>"
                data-confirm-title="Delete customer"
                data-confirm-cta="Delete"
                data-confirm-style="danger"
              >
                Delete
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>


