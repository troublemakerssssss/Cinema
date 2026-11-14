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

<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="text-white fw-bold mb-0">
            <i class="bi bi-ticket-perforated-fill me-2 text-warning"></i>My Tickets
        </h2>
        <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-warning btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Book More
        </a>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="text-center py-5 text-muted-cb">
            <i class="bi bi-ticket fs-1 mb-3 d-block"></i>
            <p>You haven't booked any tickets yet.</p>
            <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-primary">Browse Movies</a>
        </div>
    <?php else: ?>
        <div class="row g-3">
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
            <div class="col-12">
                <div class="cb-ticket">
                    <div class="ticket-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small opacity-75">BOOKING CODE</div>
                                <div class="fw-bold font-monospace fs-5"><?= sanitize($b['booking_code']) ?></div>
                            </div>
                            <span class="status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
                        </div>
                    </div>
                    <div class="ticket-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($b['poster']) ?>"
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                                     alt="" style="width:65px;height:95px;object-fit:cover;border-radius:8px">
                            </div>
                            <div class="col">
                                <h5 class="text-white fw-bold mb-2"><?= sanitize($b['movie_title']) ?></h5>
                                <div class="row g-2">
                                    <div class="col-6 col-md-3">
                                        <div class="small text-muted-cb">Date</div>
                                        <div class="text-white small fw-semibold"><?= date('d M Y', strtotime($b['show_date'])) ?></div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="small text-muted-cb">Time</div>
                                        <div class="text-white small fw-semibold"><?= date('H:i', strtotime($b['show_time'])) ?></div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="small text-muted-cb">Hall</div>
                                        <div class="text-white small fw-semibold"><?= sanitize($b['hall']) ?></div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="small text-muted-cb">Seats</div>
                                        <div class="text-white small fw-semibold"><?= sanitize($seats) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-auto text-end">
                                <div class="small text-muted-cb"><?= $b['total_seats'] ?> seat(s)</div>
                                <div class="text-warning fw-bold fs-5"><?= formatPrice($b['total_price']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="ticket-footer">
                        <small class="text-muted-cb">
                            Booked on <?= date('d M Y H:i', strtotime($b['created_at'])) ?>
                        </small>
                        <a href="<?= BASE_URL ?>/booking/confirmation.php?booking_id=<?= $b['id'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                    </div>
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
