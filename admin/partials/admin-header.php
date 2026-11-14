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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<!-- Admin Top Bar -->
<nav class="navbar navbar-dark cb-navbar px-3" style="height:60px">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/admin/index.php">
        <i class="bi bi-camera-reels-fill me-2 text-warning"></i>CineBook
        <span class="badge bg-danger ms-2 fw-normal" style="font-size:.65rem">ADMIN</span>
    </a>
    <div class="d-flex align-items-center gap-3">
        <a href="<?= BASE_URL ?>/index.php" class="text-muted-cb small text-decoration-none" target="_blank">
            <i class="bi bi-box-arrow-up-right me-1"></i>View Site
        </a>
        <span class="text-muted-cb small"><i class="bi bi-person-fill me-1"></i><?= sanitize($_SESSION['name']) ?></span>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-danger py-1 px-2">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</nav>
