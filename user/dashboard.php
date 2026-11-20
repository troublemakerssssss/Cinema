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

$confirmedBookings = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed'");
$confirmedBookings->execute([$userId]);
$confirmedBookings = (int)$confirmedBookings->fetchColumn();

// Recent bookings (last 5)
$recent = $db->prepare("
    SELECT b.*, m.title AS movie_title, m.poster, s.show_date, s.show_time, s.hall
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

    <!-- Welcome Banner (Reference 4: Modern White Dashboard) -->
    <div class="cb-dashboard-welcome mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--cb-muted);margin-bottom:.4rem">
                    Welcome back
                </div>
                <h2 class="fw-bold mb-1" style="font-size:1.75rem;color:var(--cb-text)">
                    <?= sanitize($_SESSION['name']) ?>
                </h2>
                <p style="font-size:.875rem;margin-bottom:0;color:var(--cb-muted)">
                    <?= sanitize($_SESSION['email']) ?>
                </p>
            </div>
            <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-primary">
                <i class="bi bi-camera-reels me-2"></i>Book Tickets
            </a>
        </div>
    </div>

    <!-- Stat Cards (Reference 4) -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--cb-section);color:var(--cb-text)">
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
                <div class="stat-icon" style="background:#EBF7EE;color:var(--cb-success)">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $confirmedBookings ?></div>
                    <div class="stat-label">Confirmed</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon" style="background:var(--cb-section);color:var(--cb-text)">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="stat-value" style="font-size:1.25rem;padding-top:.2rem"><?= formatPrice($totalSpent) ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="cb-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>
                <i class="bi bi-clock-history me-2" style="color:var(--cb-text)"></i>
                <strong>Recent Bookings</strong>
            </span>
            <a href="<?= BASE_URL ?>/user/my-tickets.php" class="btn btn-cb-secondary btn-sm">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="p-0">
            <?php if (empty($recentBookings)): ?>
                <div class="cb-empty">
                    <div class="empty-icon"><i class="bi bi-ticket"></i></div>
                    <h5>No Bookings Yet</h5>
                    <p>You haven't booked any tickets yet. Start by browsing movies.</p>
                    <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-primary btn-sm">
                        <i class="bi bi-camera-reels me-2"></i>Browse Movies
                    </a>
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td>
                                    <span style="font-family:monospace;font-size:.82rem;font-weight:700;color:var(--cb-text)">
                                        <?= sanitize($b['booking_code']) ?>
                                    </span>
                                </td>
                                <td style="font-weight:600;max-width:160px">
                                    <span class="d-block" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        <?= sanitize($b['movie_title']) ?>
                                    </span>
                                </td>
                                <td style="font-size:.82rem;color:var(--cb-muted)">
                                    <?= date('d M Y', strtotime($b['show_date'])) ?><br>
                                    <span style="color:var(--cb-text)"><?= date('H:i', strtotime($b['show_time'])) ?></span>
                                    — <?= sanitize($b['hall']) ?>
                                </td>
                                <td style="font-weight:600;text-align:center"><?= $b['total_seats'] ?></td>
                                <td style="font-weight:700;color:var(--cb-text)"><?= formatPrice($b['total_price']) ?></td>
                                <td><span class="status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/booking/confirmation.php?booking_id=<?= $b['id'] ?>"
                                       class="btn btn-cb-secondary btn-sm py-1 px-2" style="font-size:.75rem">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
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
