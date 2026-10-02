<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' | ServiceConnect' : 'ServiceConnect' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sc-navbar">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php"><i class="fa-solid fa-truck-medical me-2"></i>ServiceConnect</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php if (is_logged_in()): ?>
          <?php if ($_SESSION['role'] === 'user'): ?>
            <li class="nav-item"><a class="nav-link" href="/user/dashboard.php">Dashboard</a></li>
          <?php elseif ($_SESSION['role'] === 'mechanic'): ?>
            <li class="nav-item"><a class="nav-link" href="/mechanic/dashboard.php">Dashboard</a></li>
          <?php elseif ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
          <?php endif; ?>
          <li class="nav-item"><span class="nav-link text-white-50">Hi, <?= h($_SESSION['name']) ?></span></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm ms-lg-2" href="/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-light btn-sm ms-lg-2" href="/register.php">Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="container mt-3">
    <div class="alert alert-<?= h($_SESSION['flash']['type']) ?> alert-dismissible fade show" role="alert">
      <?= h($_SESSION['flash']['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
