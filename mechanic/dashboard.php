<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('mechanic');

$mechId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$mechId]);
$mechanic = $stmt->fetch();

// Pending requests (unassigned) — broadcast to all mechanics per "Asynchronous Dispatch" model
$pendingStmt = $pdo->query(
    "SELECT sr.*, u.name AS user_name, u.phone AS user_phone
     FROM service_requests sr
     JOIN users u ON u.id = sr.user_id
     WHERE sr.status = 'pending'
     ORDER BY sr.created_at ASC"
);
$pending = $pendingStmt->fetchAll();

// My active/completed jobs
$myStmt = $pdo->prepare(
    "SELECT sr.*, u.name AS user_name, u.phone AS user_phone
     FROM service_requests sr
     JOIN users u ON u.id = sr.user_id
     WHERE sr.mechanic_id = ?
     ORDER BY sr.created_at DESC"
);
$myStmt->execute([$mechId]);
$myJobs = $myStmt->fetchAll();

$pageTitle = 'Mechanic Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">

  <?php if (!$mechanic['is_verified']): ?>
    <div class="alert alert-warning">
      <i class="fa-solid fa-triangle-exclamation me-1"></i>
      Your account is <strong>pending admin verification</strong>. You can view job requests but can't accept them yet.
    </div>
  <?php endif; ?>

  <div class="row g-4 mb-2">
    <div class="col-md-4">
      <div class="sc-card p-3 text-center">
        <div class="text-muted small">Garage</div>
        <div class="fw-semibold"><?= h($mechanic['garage_name']) ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sc-card p-3 text-center">
        <div class="text-muted small">Rating</div>
        <div class="fw-semibold">
          <?= $mechanic['avg_rating'] ? h($mechanic['avg_rating']) . ' / 5 ⭐ (' . (int)$mechanic['rating_count'] . ')' : 'No ratings yet' ?>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sc-card p-3 text-center">
        <div class="text-muted small">Jobs Completed</div>
        <div class="fw-semibold"><?= count(array_filter($myJobs, fn($j) => $j['status'] === 'completed')) ?></div>
      </div>
    </div>
  </div>

  <h4 class="mt-4 mb-3">
    <i class="fa-solid fa-bell me-1"></i> Incoming Requests
    <span class="badge bg-danger" id="pendingCount"><?= count($pending) ?></span>
  </h4>

  <div class="row g-3 mb-5" id="pendingList">
    <?php if (empty($pending)): ?>
      <p class="text-muted">No pending requests right now. New requests will appear here automatically.</p>
    <?php endif; ?>
    <?php foreach ($pending as $r):
        $dist = ($mechanic['latitude'] && $mechanic['longitude'])
            ? distance_km($mechanic['latitude'], $mechanic['longitude'], $r['latitude'], $r['longitude'])
            : null;
    ?>
      <div class="col-md-6">
        <div class="sc-card p-3 border-start border-4 border-warning">
          <div class="d-flex justify-content-between">
            <h6><?= h($r['issue_type']) ?></h6>
            <?php if ($dist !== null): ?><span class="badge bg-secondary"><?= h($dist) ?> km away</span><?php endif; ?>
          </div>
          <p class="small text-muted mb-1"><?= h($r['description'] ?: 'No additional details.') ?></p>
          <p class="small mb-1"><i class="fa-solid fa-car me-1"></i><?= h($r['vehicle_info'] ?: 'Not specified') ?></p>
          <p class="small mb-2"><i class="fa-solid fa-user me-1"></i><?= h($r['user_name']) ?> &middot; <?= h($r['user_phone']) ?></p>
          <button class="btn btn-sm btn-success accept-btn" data-request-id="<?= (int)$r['id'] ?>"
                  <?= !$mechanic['is_verified'] ? 'disabled title="Pending verification"' : '' ?>>
            <i class="fa-solid fa-check me-1"></i> Accept Job
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <h4 class="mb-3"><i class="fa-solid fa-list-check me-1"></i> My Jobs</h4>
  <div class="row g-3">
    <?php if (empty($myJobs)): ?>
      <p class="text-muted">You haven't accepted any jobs yet.</p>
    <?php endif; ?>
    <?php foreach ($myJobs as $r): ?>
      <div class="col-md-6">
        <div class="sc-card p-3">
          <div class="d-flex justify-content-between">
            <h6><?= h($r['issue_type']) ?></h6>
            <span class="badge sc-badge-<?= h($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span>
          </div>
          <p class="small mb-1"><i class="fa-solid fa-user me-1"></i><?= h($r['user_name']) ?> &middot; <?= h($r['user_phone']) ?></p>
          <p class="small text-muted mb-2"><?= h($r['address_text'] ?: ($r['latitude'] . ', ' . $r['longitude'])) ?></p>
          <?php if ($r['status'] === 'accepted'): ?>
            <button class="btn btn-sm btn-primary complete-btn" data-request-id="<?= (int)$r['id'] ?>">
              <i class="fa-solid fa-flag-checkered me-1"></i> Mark Completed
            </button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
