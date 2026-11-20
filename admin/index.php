<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Stats
$stats = [
    'movies'   => (int)$db->query("SELECT COUNT(*) FROM movies")->fetchColumn(),
    'users'    => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
    'bookings' => (int)$db->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'revenue'  => (float)$db->query("SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE status='confirmed'")->fetchColumn(),
];

// Recent bookings
$recentBookings = $db->query("
    SELECT b.booking_code, b.total_price, b.status, b.created_at,
           u.name AS user_name, m.title AS movie_title
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m ON m.id = s.movie_id
    ORDER BY b.created_at DESC LIMIT 8
")->fetchAll();

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/partials/admin-header.php';
?>

<div class="cb-admin-layout">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="cb-admin-main">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color:var(--cb-text)">Dashboard</h2>
                <p class="text-muted-cb mb-0" style="font-size:.875rem">
                    Welcome back, <strong><?= sanitize($_SESSION['name']) ?></strong> —
                    <span><?= date('l, d F Y') ?></span>
                </p>
            </div>
        </div>

        <!-- Stat Cards (Reference 4) -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--cb-section);color:var(--cb-text)">
                        <i class="bi bi-camera-reels-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $stats['movies'] ?></div>
                        <div class="stat-label">Total Movies</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#EBF7EE;color:var(--cb-success)">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $stats['users'] ?></div>
                        <div class="stat-label">Registered Users</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--cb-section);color:var(--cb-text)">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value"><?= $stats['bookings'] ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--cb-section);color:var(--cb-text)">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:1.15rem;padding-top:.25rem"><?= formatPrice($stats['revenue']) ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <a href="<?= BASE_URL ?>/admin/movies.php?action=add" class="cb-card p-3 text-decoration-none d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:var(--cb-section);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--cb-text);font-size:1rem;flex-shrink:0">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <span style="font-size:.85rem;font-weight:600;color:var(--cb-text)">Add Movie</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="<?= BASE_URL ?>/admin/showtimes.php" class="cb-card p-3 text-decoration-none d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:var(--cb-section);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--cb-text);font-size:1rem;flex-shrink:0">
                        <i class="bi bi-calendar-plus-fill"></i>
                    </div>
                    <span style="font-size:.85rem;font-weight:600;color:var(--cb-text)">Add Showtime</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="<?= BASE_URL ?>/admin/bookings.php" class="cb-card p-3 text-decoration-none d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:var(--cb-section);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--cb-text);font-size:1rem;flex-shrink:0">
                        <i class="bi bi-ticket-perforated-fill"></i>
                    </div>
                    <span style="font-size:.85rem;font-weight:600;color:var(--cb-text)">Bookings</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="<?= BASE_URL ?>/admin/users.php" class="cb-card p-3 text-decoration-none d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;background:var(--cb-section);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--cb-text);font-size:1rem;flex-shrink:0">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span style="font-size:.85rem;font-weight:600;color:var(--cb-text)">Users</span>
                </a>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="cb-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>
                    <i class="bi bi-clock-history me-2" style="color:var(--cb-text)"></i>
                    <strong>Recent Bookings</strong>
                </span>
                <a href="<?= BASE_URL ?>/admin/bookings.php" class="btn btn-cb-secondary btn-sm">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>User</th>
                            <th>Movie</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBookings)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted-cb">No bookings yet.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td>
                                <span style="font-family:monospace;font-size:.82rem;font-weight:700;color:var(--cb-text)">
                                    <?= sanitize($b['booking_code']) ?>
                                </span>
                            </td>
                            <td style="font-weight:600"><?= sanitize($b['user_name']) ?></td>
                            <td style="max-width:160px">
                                <span class="d-block" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    <?= sanitize($b['movie_title']) ?>
                                </span>
                            </td>
                            <td style="font-weight:700;color:var(--cb-text)"><?= formatPrice($b['total_price']) ?></td>
                            <td><span class="status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            <td style="color:var(--cb-muted);font-size:.82rem"><?= date('d M Y H:i', strtotime($b['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/admin-footer.php'; ?>
