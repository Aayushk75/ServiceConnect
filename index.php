<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>

<section class="sc-hero text-white text-center">
  <div class="container py-5">
    <h1 class="display-5 fw-bold mb-3">Stranded on the road? Help is a click away.</h1>
    <p class="lead mb-4">ServiceConnect connects you with verified, nearby mechanics in real time —
      no phone calls, no guesswork, no waiting around.</p>
    <?php if (!is_logged_in()): ?>
      <a href="/register.php" class="btn btn-light btn-lg me-2">Get Started</a>
      <a href="/login.php" class="btn btn-outline-light btn-lg">Login</a>
    <?php elseif ($_SESSION['role'] === 'user'): ?>
      <a href="/user/dashboard.php" class="btn btn-light btn-lg">Request Assistance</a>
    <?php elseif ($_SESSION['role'] === 'mechanic'): ?>
      <a href="/mechanic/dashboard.php" class="btn btn-light btn-lg">View Job Requests</a>
    <?php else: ?>
      <a href="/admin/dashboard.php" class="btn btn-light btn-lg">Admin Panel</a>
    <?php endif; ?>
  </div>
</section>

<section class="container py-5">
  <div class="row g-4 text-center">
    <div class="col-md-4">
      <div class="sc-card p-4 h-100">
        <i class="fa-solid fa-location-crosshairs fa-2x sc-icon mb-3"></i>
        <h5>Instant Location Match</h5>
        <p class="text-muted">Your breakdown location is matched against nearby verified mechanics automatically.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sc-card p-4 h-100">
        <i class="fa-solid fa-bolt fa-2x sc-icon mb-3"></i>
        <h5>Real-Time Dispatch</h5>
        <p class="text-muted">Requests broadcast to multiple mechanics at once — first to accept gets the job.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sc-card p-4 h-100">
        <i class="fa-solid fa-star fa-2x sc-icon mb-3"></i>
        <h5>Verified &amp; Rated</h5>
        <p class="text-muted">Every mechanic is admin-verified and rated by real customers after each job.</p>
      </div>
    </div>
  </div>
</section>

<section class="container pb-5">
  <div class="row align-items-center">
    <div class="col-md-6">
      <h3 class="mb-3">How it works</h3>
      <ol class="sc-steps">
        <li><strong>Register &amp; Login</strong> — as a driver or mechanic.</li>
        <li><strong>Submit a request</strong> — describe the issue and share your location.</li>
        <li><strong>Get matched</strong> — nearby mechanics are notified instantly.</li>
        <li><strong>Get help</strong> — track status until the job is marked complete.</li>
      </ol>
    </div>
    <div class="col-md-6 text-center">
      <i class="fa-solid fa-car-burst" style="font-size: 9rem; color: var(--sc-primary); opacity: .15;"></i>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
