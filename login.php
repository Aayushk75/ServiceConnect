<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: /index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] === 'mechanic' && !$user['is_verified']) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Your mechanic account is pending admin verification. You can browse your dashboard but cannot accept jobs yet.'
            ];
        }

        $redirect = match ($user['role']) {
            'mechanic' => '/mechanic/dashboard.php',
            'admin'    => '/admin/dashboard.php',
            default    => '/user/dashboard.php',
        };
        header("Location: $redirect");
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="sc-card p-4 p-md-5">
        <h3 class="mb-4 text-center">Welcome back</h3>

        <?php if ($error): ?>
          <div class="alert alert-danger py-2"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required autofocus>
          </div>
          <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Log In</button>
        </form>

        <p class="text-center mt-3 mb-0">New here? <a href="/register.php">Create an account</a></p>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
