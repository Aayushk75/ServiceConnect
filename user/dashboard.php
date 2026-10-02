<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('user');

$stmt = $pdo->prepare(
    "SELECT sr.*, u.name AS mechanic_name, u.phone AS mechanic_phone, u.garage_name, u.avg_rating
     FROM service_requests sr
     LEFT JOIN users u ON u.id = sr.mechanic_id
     WHERE sr.user_id = ?
     ORDER BY sr.created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();

$pageTitle = 'My Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="mb-0">My Requests</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRequestModal">
      <i class="fa-solid fa-circle-plus me-1"></i> New Request
    </button>
  </div>

  <?php if (empty($requests)): ?>
    <div class="sc-card p-5 text-center text-muted">
      <i class="fa-solid fa-clipboard-list fa-2x mb-3"></i>
      <p class="mb-0">You haven't submitted any assistance requests yet.</p>
    </div>
  <?php else: ?>
    <div class="row g-3" id="requestList">
      <?php foreach ($requests as $r): ?>
        <div class="col-md-6">
          <div class="sc-card p-3">
            <div class="d-flex justify-content-between align-items-start">
              <h6 class="mb-1"><?= h($r['issue_type']) ?></h6>
              <span class="badge sc-badge-<?= h($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span>
            </div>
            <p class="text-muted small mb-2"><?= h($r['description'] ?: 'No additional details.') ?></p>
            <p class="small mb-1"><i class="fa-solid fa-location-dot me-1"></i><?= h($r['address_text'] ?: ($r['latitude'] . ', ' . $r['longitude'])) ?></p>
            <?php if ($r['mechanic_name']): ?>
              <hr class="my-2">
              <p class="small mb-0">
                <i class="fa-solid fa-user-gear me-1"></i>
                <strong><?= h($r['mechanic_name']) ?></strong> (<?= h($r['garage_name'] ?? '') ?>)
                <?php if ($r['mechanic_phone']): ?> &middot; <a href="tel:<?= h($r['mechanic_phone']) ?>"><?= h($r['mechanic_phone']) ?></a><?php endif; ?>
              </p>
            <?php endif; ?>
            <p class="small text-muted mt-2 mb-0"><?= h(date('M j, g:i A', strtotime($r['created_at']))) ?></p>

            <?php if ($r['status'] === 'completed'): ?>
              <button class="btn btn-sm btn-outline-primary mt-2 rate-btn"
                      data-request-id="<?= (int)$r['id'] ?>"
                      data-bs-toggle="modal" data-bs-target="#rateModal">
                <i class="fa-solid fa-star me-1"></i> Rate Mechanic
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- New Request Modal -->
<div class="modal fade" id="newRequestModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="newRequestForm">
        <div class="modal-header">
          <h5 class="modal-title">Request Roadside Assistance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="requestFormAlert"></div>

          <div class="mb-3">
            <label class="form-label">Issue Type</label>
            <select name="issue_type" class="form-select" required>
              <option value="">Select an issue...</option>
              <option>Flat Tyre</option>
              <option>Battery / Won't Start</option>
              <option>Engine Trouble</option>
              <option>Out of Fuel</option>
              <option>Towing Needed</option>
              <option>Accident / Collision</option>
              <option>Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Vehicle Info</label>
            <input type="text" name="vehicle_info" class="form-control" placeholder="e.g. Honda City, White, DL 3C 1234">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Anything mechanics should know..."></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Your Location</label>
            <div class="input-group">
              <input type="text" id="locationDisplay" class="form-control" placeholder="Click 'Detect' to share GPS location" readonly>
              <button class="btn btn-outline-secondary" type="button" id="detectLocationBtn">
                <i class="fa-solid fa-location-crosshairs"></i> Detect
              </button>
            </div>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <small class="text-muted">Your browser will ask for location permission.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submitRequestBtn">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Rate Mechanic Modal -->
<div class="modal fade" id="rateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="rateForm">
        <div class="modal-header">
          <h5 class="modal-title">Rate this mechanic</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="request_id" id="rateRequestId">
          <div class="mb-3 sc-star-rating" id="starRating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <i class="fa-solid fa-star" data-value="<?= $i ?>"></i>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="ratingValue" value="5">
          <div class="mb-2">
            <label class="form-label">Comment (optional)</label>
            <textarea name="comment" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Rating</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
