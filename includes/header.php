<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = $page_title ?? 'CineBook';
$current_page = $current_page ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($page_title) ?> — CineBook</title>
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS with Cache-Busting -->
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time() ?>" rel="stylesheet">
    <script>var BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg cb-navbar sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <span class="brand-icon"><i class="bi bi-camera-reels-fill"></i></span>
            <span class="brand-name">CineBook</span>
        </a>

        <!-- Mobile toggler -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav items -->
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto ms-3 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'home' ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="bi bi-house me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'movies' ? 'active' : '' ?>" href="<?= BASE_URL ?>/movies.php">
                        <i class="bi bi-camera-reels me-1"></i>Movies
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/index.php">
                                <i class="bi bi-speedometer2 me-1"></i>Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:32px;height:32px;background:var(--cb-section);border:1px solid var(--cb-border)">
                                <i class="bi bi-person-fill" style="color:var(--cb-text);font-size:.85rem"></i>
                            </span>
                            <span class="d-none d-md-inline" style="font-size:.875rem;font-weight:600;color:var(--cb-text)"><?= sanitize($_SESSION['name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end cb-dropdown">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/user/dashboard.php">
                                    <i class="bi bi-grid-1x2 me-2" style="color:var(--cb-text)"></i>Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/user/my-tickets.php">
                                    <i class="bi bi-ticket-perforated me-2" style="color:var(--cb-text)"></i>My Tickets
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page === 'register' ? 'active' : '' ?>" href="<?= BASE_URL ?>/register.php">
                            Register
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-cb-primary btn-sm px-4" href="<?= BASE_URL ?>/login.php">
                            Sign In
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<!-- END NAVBAR -->
