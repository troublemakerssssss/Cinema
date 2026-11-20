<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

// Pagination
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Count
$countStmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$countStmt->execute([$userId]);
$total     = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

// Fetch
$stmt = $db->prepare("
    SELECT b.*, m.title AS movie_title, m.poster,
           s.show_date, s.show_time, s.hall
    FROM bookings b
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m    ON m.id = s.movie_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$userId, $perPage, $offset]);
$bookings = $stmt->fetchAll();

$page_title   = 'My Tickets';
$current_page = 'tickets';
require_once __DIR__ . '/../includes/header.php';
?>
<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<!-- Page Header -->
<div style="background:var(--cb-white);border-bottom:1px solid var(--cb-border)">
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1" style="color:var(--cb-text)">
                    <i class="bi bi-ticket-perforated-fill me-2" style="color:var(--cb-text)"></i>My Tickets
                </h1>
                <p class="text-muted-cb mb-0" style="font-size:.875rem"><?= $total ?> booking<?= $total !== 1 ? 's' : '' ?> found</p>
            </div>
            <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Book More Tickets
            </a>
        </div>
    </div>
</div>

<div class="container py-4">
    <?php if (empty($bookings)): ?>
        <div class="cb-empty">
            <div class="empty-icon"><i class="bi bi-ticket"></i></div>
            <h5>No Tickets Yet</h5>
            <p>You haven't booked any tickets yet.</p>
            <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-primary">
                <i class="bi bi-camera-reels me-2"></i>Browse Movies
            </a>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($bookings as $b):
                $seatStmt = $db->prepare("
                    SELECT CONCAT(se.seat_row, se.seat_number) AS code
                    FROM booking_details bd
                    JOIN seats se ON se.id = bd.seat_id
                    WHERE bd.booking_id = ?
                    ORDER BY se.seat_row, se.seat_number
                ");
                $seatStmt->execute([$b['id']]);
                $seats = implode(', ', $seatStmt->fetchAll(PDO::FETCH_COLUMN));
            ?>
            <div class="cb-ticket">
                <!-- Header -->
                <div class="ticket-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;opacity:.75;margin-bottom:.2rem">Booking Code</div>
                            <div style="font-weight:800;font-family:monospace;font-size:1.1rem;letter-spacing:.04em">
                                <?= sanitize($b['booking_code']) ?>
                            </div>
                        </div>
                        <span class="status-<?= $b['status'] ?>" style="background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.3);color:#fff">
                            <?= ucfirst($b['status']) ?>
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="ticket-body">
                    <div class="row g-3 align-items-center">
                        <!-- Poster -->
                        <div class="col-auto">
                            <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($b['poster']) ?>"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                                 alt="" style="width:60px;height:88px;object-fit:cover;border-radius:var(--cb-radius-sm)">
                        </div>
                        <!-- Info -->
                        <div class="col">
                            <h5 style="font-weight:700;font-size:.95rem;color:var(--cb-text);margin-bottom:.6rem">
                                <?= sanitize($b['movie_title']) ?>
                            </h5>
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted);font-weight:600">Date</div>
                                    <div style="font-size:.82rem;font-weight:600;color:var(--cb-text)"><?= date('d M Y', strtotime($b['show_date'])) ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted);font-weight:600">Time</div>
                                    <div style="font-size:.82rem;font-weight:600;color:var(--cb-text)"><?= date('H:i', strtotime($b['show_time'])) ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted);font-weight:600">Hall</div>
                                    <div style="font-size:.82rem;font-weight:600;color:var(--cb-text)"><?= sanitize($b['hall']) ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted);font-weight:600">Seats</div>
                                    <div style="font-size:.82rem;font-weight:600;color:var(--cb-text)"><?= sanitize($seats) ?></div>
                                </div>
                            </div>
                        </div>
                        <!-- Price -->
                        <div class="col-md-auto text-md-end">
                            <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-muted);font-weight:600"><?= $b['total_seats'] ?> seat<?= $b['total_seats'] > 1 ? 's' : '' ?></div>
                            <div style="font-weight:800;font-size:1.1rem;color:var(--cb-text)"><?= formatPrice($b['total_price']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="ticket-footer">
                    <small style="color:var(--cb-text-muted);font-size:.78rem">
                        <i class="bi bi-calendar3 me-1"></i>Booked on <?= date('d M Y H:i', strtotime($b['created_at'])) ?>
                    </small>
                    <a href="<?= BASE_URL ?>/booking/confirmation.php?booking_id=<?= $b['id'] ?>"
                       class="btn btn-cb-secondary btn-sm">
                        <i class="bi bi-eye me-1"></i>View Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>"><i class="bi bi-chevron-left"></i></a>
                </li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>"><i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
