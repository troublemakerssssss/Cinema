<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

// Stats
$totalBookings   = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$totalBookings->execute([$userId]);
$totalBookings   = (int)$totalBookings->fetchColumn();

$totalSpent = $db->prepare("SELECT COALESCE(SUM(total_price),0) FROM bookings WHERE user_id = ? AND status = 'confirmed'");
$totalSpent->execute([$userId]);
$totalSpent = (float)$totalSpent->fetchColumn();

// Recent bookings (last 5)
$recent = $db->prepare("
    SELECT b.*, m.title AS movie_title, s.show_date, s.show_time, s.hall
    FROM bookings b
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m ON m.id = s.movie_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
");
$recent->execute([$userId]);
$recentBookings = $recent->fetchAll();

$page_title   = 'My Dashboard';
$current_page = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="text-white fw-bold mb-1">
                <i class="bi bi-person-circle me-2 text-warning"></i>My Dashboard
            </h2>
            <p class="text-muted-cb mb-0">Welcome back, <?= sanitize($_SESSION['name']) ?>!</p>
        </div>
        <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-warning">
            <i class="bi bi-camera-reels me-2"></i>Book Tickets
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(26,115,232,.2); color:#6eb4ff">
                    <i class="bi bi-ticket-perforated-fill"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $totalBookings ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(249,168,37,.15); color:var(--cb-warn)">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.3rem"><?= formatPrice($totalSpent) ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(76,175,80,.15); color:#81c784">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1rem; padding-top:.3rem"><?= sanitize($_SESSION['email']) ?></div>
                    <div class="stat-label">Account Email</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="cb-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history me-2 text-warning"></i>Recent Bookings</span>
            <a href="<?= BASE_URL ?>/user/my-tickets.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="p-0">
            <?php if (empty($recentBookings)): ?>
                <div class="text-center py-5 text-muted-cb">
                    <i class="bi bi-ticket fs-1 mb-2 d-block"></i>
                    No bookings yet.
                    <br><a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-primary btn-sm mt-3">Book Now</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table cb-table mb-0">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Movie</th>
                                <th>Date &amp; Time</th>
                                <th>Seats</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td class="font-monospace text-warning"><?= sanitize($b['booking_code']) ?></td>
                                <td class="text-white fw-semibold"><?= sanitize($b['movie_title']) ?></td>
                                <td class="text-muted-cb small">
                                    <?= date('d M Y', strtotime($b['show_date'])) ?><br>
                                    <?= date('H:i', strtotime($b['show_time'])) ?>
                                    — <?= sanitize($b['hall']) ?>
                                </td>
                                <td><?= $b['total_seats'] ?></td>
                                <td class="text-warning fw-semibold"><?= formatPrice($b['total_price']) ?></td>
                                <td><span class="status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
