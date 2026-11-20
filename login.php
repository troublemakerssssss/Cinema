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
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: ' . BASE_URL . '/admin/index.php');
            } else {
                header('Location: ' . BASE_URL . '/user/dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$page_title   = 'Login';
$current_page = 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — CineBook</title>
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
        <div class="col-sm-9 col-md-6 col-lg-4">

            <!-- Logo / Brand -->
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none d-inline-flex align-items-center gap-2 mb-3">
                    <span style="width:42px;height:42px;background:var(--cb-button);border-radius:12px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.15rem">
                        <i class="bi bi-camera-reels-fill"></i>
                    </span>
                    <span style="font-size:1.35rem;font-weight:800;color:var(--cb-text)">CineBook</span>
                </a>
                <h1 class="h4 fw-bold mb-1" style="color:var(--cb-text)">Welcome back</h1>
                <p style="font-size:.875rem;color:var(--cb-muted)">Sign in to your account to continue</p>
            </div>

            <!-- Card -->
            <div class="cb-card p-4">
                <?php if ($error): ?>
                    <div class="cb-alert-danger mb-3" data-auto-dismiss="5000">
                        <i class="bi bi-exclamation-circle-fill"></i><?= sanitize($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="cb-label" for="email">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control cb-form-control"
                                   placeholder="you@example.com"
                                   value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="cb-label" for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control cb-form-control"
                                   placeholder="Enter your password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-cb-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>
            </div>

            <p class="text-center mt-3" style="font-size:.875rem;color:var(--cb-muted)">
                Don't have an account?
                <a href="<?= BASE_URL ?>/register.php" class="fw-semibold text-decoration-underline" style="color:var(--cb-text)">Create one free</a>
            </p>

            <p class="text-center" style="font-size:.75rem;color:var(--cb-muted);margin-top:.5rem">
                Demo admin: <code>admin@cinema.com</code> / <code>password</code>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
