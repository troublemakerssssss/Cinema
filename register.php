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

            $success = 'Registration successful! You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — CineBook</title>
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= file_exists(__DIR__ . '/assets/css/style.css') ? filemtime(__DIR__ . '/assets/css/style.css') : time() ?>" rel="stylesheet">
</head>
<body style="background:var(--cb-white);min-height:100vh;display:flex;align-items:center;padding:2rem 0">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5">

            <!-- Logo / Brand -->
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
                    <span style="width:42px;height:42px;background:var(--cb-button);border-radius:12px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.15rem">
                        <i class="bi bi-camera-reels-fill"></i>
                    </span>
                    <span style="font-size:1.35rem;font-weight:800;color:var(--cb-text)">CineBook</span>
                </a>
                <h1 class="h4 fw-bold mb-1" style="color:var(--cb-text)">Create your account</h1>
                <p style="font-size:.875rem;color:var(--cb-muted)">Join CineBook and start booking movie tickets</p>
            </div>

            <!-- Card -->
            <div class="cb-card p-4">
                <?php if ($error): ?>
                    <div class="cb-alert-danger mb-3" data-auto-dismiss="6000">
                        <i class="bi bi-exclamation-circle-fill"></i><?= sanitize($error) ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="cb-alert-success mb-3">
                        <i class="bi bi-check-circle-fill"></i>
                        <?= sanitize($success) ?>
                        <a href="<?= BASE_URL ?>/login.php" class="fw-bold ms-1" style="color:var(--cb-success)">Sign in now →</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="cb-label" for="name">Full Name <span style="color:var(--cb-danger)">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-person"></i></span>
                            <input type="text" id="name" name="name" class="form-control cb-form-control"
                                   placeholder="Your full name"
                                   value="<?= sanitize($_POST['name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="cb-label" for="email">Email Address <span style="color:var(--cb-danger)">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control cb-form-control"
                                   placeholder="you@example.com"
                                   value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="cb-label" for="phone">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-telephone"></i></span>
                            <input type="tel" id="phone" name="phone" class="form-control cb-form-control"
                                   placeholder="+62 812-xxxx-xxxx"
                                   value="<?= sanitize($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="cb-label" for="password">Password <span style="color:var(--cb-danger)">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control cb-form-control"
                                   placeholder="Min. 6 characters" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="cb-label" for="confirm">Confirm Password <span style="color:var(--cb-danger)">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" id="confirm" name="confirm" class="form-control cb-form-control"
                                   placeholder="Repeat your password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-cb-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-person-check me-2"></i>Create Account
                    </button>
                </form>
            </div>

            <p class="text-center mt-3" style="font-size:.875rem;color:var(--cb-muted)">
                Already have an account?
                <a href="<?= BASE_URL ?>/login.php" class="fw-semibold text-decoration-underline" style="color:var(--cb-text)">Sign in here</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
