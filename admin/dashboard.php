<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$mechanics = $pdo->query(
    "SELECT * FROM users WHERE role = 'mechanic' ORDER BY is_verified ASC, created_at DESC"
)->fetchAll();

$allRequests = $pdo->query(
    "SELECT sr.*, u.name AS user_name, m.name AS mechanic_name
     FROM service_requests sr
     JOIN users u ON u.id = sr.user_id
     LEFT JOIN users m ON m.id = sr.mechanic_id
     ORDER BY sr.created_at DESC LIMIT 50"
)->fetchAll();

$stats = [
    'total_users'     => $pdo->query("SELECT COUNT(*) c FROM users WHERE role='user'")->fetch()['c'],
    'total_mechanics' => $pdo->query("SELECT COUNT(*) c FROM users WHERE role='mechanic'")->fetch()['c'],
    'pending_verify'  => $pdo->query("SELECT COUNT(*) c FROM users WHERE role='mechanic' AND is_verified=0")->fetch()['c'],
    'total_requests'  => $pdo->query("SELECT COUNT(*) c FROM service_requests")->fetch()['c'],
    'completed'       => $pdo->query("SELECT COUNT(*) c FROM service_requests WHERE status='completed'")->fetch()['c'],
];

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <h3 class="mb-4">Platform Overview</h3>

  <div class="row g-3 mb-5">
    <div class="col-6 col-md-2-4 col-md">
      <div class="sc-card p-3 text-center">
        <div class="fs-4 fw-bold"><?= (int)$stats['total_users'] ?></div>
        <div class="text-muted small">Drivers</div>
      </div>
    </div>
    <div class="col-6 col-md">
      <div class="sc-card p-3 text-center">
        <div class="fs-4 fw-bold"><?= (int)$stats['total_mechanics'] ?></div>
        <div class="text-muted small">Mechanics</div>
      </div>
    </div>
    <div class="col-6 col-md">
      <div class="sc-card p-3 text-center">
        <div class="fs-4 fw-bold text-warning"><?= (int)$stats['pending_verify'] ?></div>
        <div class="text-muted small">Pending Verification</div>
      </div>
    </div>
    <div class="col-6 col-md">
      <div class="sc-card p-3 text-center">
        <div class="fs-4 fw-bold"><?= (int)$stats['total_requests'] ?></div>
        <div class="text-muted small">Total Requests</div>
      </div>
    </div>
    <div class="col-6 col-md">
      <div class="sc-card p-3 text-center">
        <div class="fs-4 fw-bold text-success"><?= (int)$stats['completed'] ?></div>
        <div class="text-muted small">Completed</div>
      </div>
    </div>
  </div>

  <h4 class="mb-3">Mechanic Verification</h4>
  <div class="table-responsive mb-5">
    <table class="table sc-table align-middle">
      <thead>
        <tr><th>Name</th><th>Garage</th><th>Specialization</th><th>Rating</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($mechanics as $m): ?>
          <tr>
            <td><?= h($m['name']) ?><br><small class="text-muted"><?= h($m['email']) ?></small></td>
            <td><?= h($m['garage_name']) ?></td>
            <td><?= h($m['specialization']) ?></td>
            <td><?= $m['avg_rating'] ? h($m['avg_rating']) . ' ⭐' : '—' ?></td>
            <td>
              <?php if ($m['is_verified']): ?>
                <span class="badge bg-success">Verified</span>
              <?php else: ?>
                <span class="badge bg-warning text-dark">Pending</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!$m['is_verified']): ?>
                <button class="btn btn-sm btn-success verify-btn" data-user-id="<?= (int)$m['id'] ?>">Verify</button>
              <?php else: ?>
                <button class="btn btn-sm btn-outline-danger revoke-btn" data-user-id="<?= (int)$m['id'] ?>">Revoke</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($mechanics)): ?>
          <tr><td colspan="6" class="text-center text-muted py-3">No mechanics registered yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <h4 class="mb-3">All Service Requests</h4>
  <div class="table-responsive">
    <table class="table sc-table align-middle">
      <thead>
        <tr><th>#</th><th>Driver</th><th>Issue</th><th>Mechanic</th><th>Status</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php foreach ($allRequests as $r): ?>
          <tr>
            <td>#<?= (int)$r['id'] ?></td>
            <td><?= h($r['user_name']) ?></td>
            <td><?= h($r['issue_type']) ?></td>
            <td><?= h($r['mechanic_name'] ?? '—') ?></td>
            <td><span class="badge sc-badge-<?= h($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span></td>
            <td><small><?= h(date('M j, g:i A', strtotime($r['created_at']))) ?></small></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
