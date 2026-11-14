<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $ins = $db->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?,?,?,?,'user')");
            $ins->execute([$name, $email, $hashed, $phone]);

            $success = 'Registration successful! You can now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — CineBook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center py-5" style="min-height:100vh">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5">
            <!-- Logo -->
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">
                    <i class="bi bi-camera-reels-fill fs-1 text-warning"></i>
                    <h4 class="text-white fw-bold mt-2">CineBook</h4>
                </a>
                <p class="text-muted-cb small">Create your free account</p>
            </div>

            <div class="cb-card p-4">
                <?php if ($error): ?>
                    <div class="cb-alert-danger mb-3" data-auto-dismiss="5000">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= sanitize($error) ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="cb-alert-success mb-3">
                        <i class="bi bi-check-circle me-2"></i><?= sanitize($success) ?>
                        <a href="<?= BASE_URL ?>/login.php" class="fw-bold text-success ms-1">Login Now</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="cb-label">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-cb-mid border-cb text-muted-cb"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" class="form-control cb-form-control"
                                   placeholder="Your full name"
                                   value="<?= sanitize($_POST['name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="cb-label">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-cb-mid border-cb text-muted-cb"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control cb-form-control"
                                   placeholder="you@example.com"
                                   value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="cb-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-cb-mid border-cb text-muted-cb"><i class="bi bi-telephone"></i></span>
                            <input type="tel" name="phone" class="form-control cb-form-control"
                                   placeholder="+62 812-xxxx-xxxx"
                                   value="<?= sanitize($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="cb-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-cb-mid border-cb text-muted-cb"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control cb-form-control"
                                   placeholder="Min. 6 characters" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="cb-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-cb-mid border-cb text-muted-cb"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="confirm" class="form-control cb-form-control"
                                   placeholder="Repeat your password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-cb-primary w-100 py-2">
                        <i class="bi bi-person-check me-2"></i>Create Account
                    </button>
                </form>
            </div>

            <p class="text-center mt-3 text-muted-cb small">
                Already have an account?
                <a href="<?= BASE_URL ?>/login.php" class="text-warning fw-semibold">Login here</a>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
