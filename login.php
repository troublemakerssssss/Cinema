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
    <title>Login — CineBook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-9 col-md-6 col-lg-4">
            <!-- Logo -->
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">
                    <i class="bi bi-camera-reels-fill fs-1 text-warning"></i>
                    <h4 class="text-white fw-bold mt-2">CineBook</h4>
                </a>
                <p class="text-muted-cb small">Sign in to your account</p>
            </div>

            <!-- Card -->
            <div class="cb-card p-4">
                <?php if ($error): ?>
                    <div class="cb-alert-danger mb-3" data-auto-dismiss="4000">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= sanitize($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="cb-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-cb-mid border-cb text-muted-cb"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control cb-form-control"
                                   placeholder="you@example.com"
                                   value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="cb-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-cb-mid border-cb text-muted-cb"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control cb-form-control"
                                   placeholder="Enter your password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-cb-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>
            </div>

            <p class="text-center mt-3 text-muted-cb small">
                Don't have an account?
                <a href="<?= BASE_URL ?>/register.php" class="text-warning fw-semibold">Register here</a>
            </p>
            <p class="text-center text-muted-cb small" style="font-size:.75rem">
                Demo admin: admin@cinema.com / password
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
