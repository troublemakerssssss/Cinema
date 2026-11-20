<?php
// Admin-specific header (no main navbar)
$page_title = $page_title ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($page_title) ?> — CineBook Admin</title>
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= file_exists(__DIR__ . '/../../assets/css/style.css') ? filemtime(__DIR__ . '/../../assets/css/style.css') : time() ?>" rel="stylesheet">
    <script>var BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body style="background:var(--cb-section)">

<!-- Admin Top Bar -->
<nav class="cb-admin-navbar">
    <a class="admin-brand" href="<?= BASE_URL ?>/admin/index.php">
        <span class="brand-icon"><i class="bi bi-camera-reels-fill"></i></span>
        <span>CineBook</span>
        <span class="admin-badge">ADMIN</span>
    </a>
    <div class="admin-actions">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-cb-secondary btn-sm" target="_blank" rel="noopener">
            <i class="bi bi-box-arrow-up-right me-1"></i><span class="d-none d-md-inline">View Site</span>
        </a>
        <span style="font-size:.85rem;color:var(--cb-muted)" class="d-none d-md-inline">
            <i class="bi bi-person-fill me-1"></i><?= sanitize($_SESSION['name']) ?>
        </span>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-cb-secondary btn-sm" title="Sign Out">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</nav>
