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

<div class="d-flex" style="min-height:calc(100vh - 60px)">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-grow-1 p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="text-white fw-bold mb-1">Dashboard</h2>
                <p class="text-muted-cb mb-0">Welcome, <?= sanitize($_SESSION['name']) ?>!</p>
            </div>
            <span class="text-muted-cb small"><i class="bi bi-calendar me-1"></i><?= date('l, d F Y') ?></span>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(26,115,232,.2);color:#6eb4ff"><i class="bi bi-camera-reels-fill"></i></div>
                    <div>
                        <div class="stat-value"><?= $stats['movies'] ?></div>
                        <div class="stat-label">Movies</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(76,175,80,.15);color:#81c784"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="stat-value"><?= $stats['users'] ?></div>
                        <div class="stat-label">Users</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(156,39,176,.15);color:#ce93d8"><i class="bi bi-ticket-perforated-fill"></i></div>
                    <div>
                        <div class="stat-value"><?= $stats['bookings'] ?></div>
                        <div class="stat-label">Bookings</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(249,168,37,.15);color:var(--cb-warn)"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="stat-value" style="font-size:1rem"><?= formatPrice($stats['revenue']) ?></div>
                        <div class="stat-label">Revenue</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="cb-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2 text-warning"></i>Recent Bookings</span>
                <a href="<?= BASE_URL ?>/admin/bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead><tr>
                        <th>Code</th><th>User</th><th>Movie</th><th>Total</th><th>Status</th><th>Date</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td class="font-monospace text-warning small"><?= sanitize($b['booking_code']) ?></td>
                            <td><?= sanitize($b['user_name']) ?></td>
                            <td><?= sanitize($b['movie_title']) ?></td>
                            <td class="text-warning"><?= formatPrice($b['total_price']) ?></td>
                            <td><span class="status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            <td class="small text-muted-cb"><?= date('d M Y H:i', strtotime($b['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/admin-footer.php'; ?>
