<?php
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: /index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? 'user';
    $garage_name    = trim($_POST['garage_name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        $errors[] = 'Please fill in all required fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (!in_array($role, ['user', 'mechanic'], true)) {
        $errors[] = 'Invalid role selected.';
    }
    if ($role === 'mechanic' && $garage_name === '') {
        $errors[] = 'Garage/shop name is required for mechanic accounts.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // Mechanics start unverified; admin must verify them (per Trust & Verification research insight)
        $isVerified = 1;

        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, phone, role, garage_name, specialization, is_verified)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $name, $email, $hash, $phone, $role,
            $role === 'mechanic' ? $garage_name : null,
            $role === 'mechanic' ? $specialization : null,
            $isVerified
        ]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => $role === 'mechanic'
                ? 'Account created! An admin will verify your garage before you can accept jobs.'
                : 'Account created successfully. Please log in.'
        ];
        header('Location: /login.php');
        exit;
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
      <div class="sc-card p-4 p-md-5">
        <h3 class="mb-4 text-center">Create your account</h3>

        <?php foreach ($errors as $err): ?>
          <div class="alert alert-danger py-2"><?= h($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" id="registerForm" novalidate>
          <div class="mb-3">
            <label class="form-label">I am a...</label>
            <div class="btn-group w-100" role="group">
              <input type="radio" class="btn-check" name="role" id="roleUser" value="user" checked>
              <label class="btn btn-outline-primary" for="roleUser"><i class="fa-solid fa-car me-1"></i> Driver</label>

              <input type="radio" class="btn-check" name="role" id="roleMechanic" value="mechanic">
              <label class="btn btn-outline-primary" for="roleMechanic"><i class="fa-solid fa-wrench me-1"></i> Mechanic</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required value="<?= h($_POST['name'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= h($_POST['email'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control" required value="<?= h($_POST['phone'] ?? '') ?>">
          </div>

          <div id="mechanicFields" class="d-none">
            <div class="mb-3">
              <label class="form-label">Garage / Shop Name</label>
              <input type="text" name="garage_name" class="form-control" value="<?= h($_POST['garage_name'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Specialization</label>
              <input type="text" name="specialization" class="form-control" placeholder="e.g. Engine, Tyres, Electrical" value="<?= h($_POST['specialization'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="6">
          </div>
          <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
          </div>

          <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>

        <p class="text-center mt-3 mb-0">Already have an account? <a href="/login.php">Log in</a></p>
      </div>
    </div>
  </div>
</div>

<script>
  const roleUser = document.getElementById('roleUser');
  const roleMechanic = document.getElementById('roleMechanic');
  const mechanicFields = document.getElementById('mechanicFields');
  function toggleFields() {
    mechanicFields.classList.toggle('d-none', !roleMechanic.checked);
  }
  roleUser.addEventListener('change', toggleFields);
  roleMechanic.addEventListener('change', toggleFields);
  toggleFields();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
