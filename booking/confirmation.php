<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$booking_id = (int)($_GET['booking_id'] ?? 0);
if ($booking_id <= 0) {
    header('Location: ' . BASE_URL . '/user/my-tickets.php');
    exit;
}

$db = getDB();

// Fetch booking (must belong to this user)
$stmt = $db->prepare("
    SELECT b.*, m.title AS movie_title, m.poster,
           s.show_date, s.show_time, s.hall, s.price
    FROM bookings b
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m ON m.id = s.movie_id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: ' . BASE_URL . '/user/my-tickets.php');
    exit;
}

// Fetch booked seats
$seatStmt = $db->prepare("
    SELECT se.seat_row, se.seat_number
    FROM booking_details bd
    JOIN seats se ON se.id = bd.seat_id
    WHERE bd.booking_id = ?
    ORDER BY se.seat_row, se.seat_number
");
$seatStmt->execute([$booking_id]);
$seatList = $seatStmt->fetchAll();
$seatCodes = array_map(fn($s) => $s['seat_row'] . $s['seat_number'], $seatList);

$page_title   = 'Booking Confirmed';
$current_page = 'movies';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Success Banner -->
            <div class="text-center mb-4">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:3.5rem"></i>
                </div>
                <h2 class="text-white fw-bold">Booking Confirmed!</h2>
                <p class="text-muted-cb">Your tickets have been successfully booked. Enjoy the movie!</p>
            </div>

            <!-- Ticket Card -->
            <div class="cb-ticket">
                <!-- Header -->
                <div class="ticket-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small opacity-75 mb-1">BOOKING CODE</div>
                            <div class="fs-4 fw-bold font-monospace"><?= sanitize($booking['booking_code']) ?></div>
                        </div>
                        <i class="bi bi-camera-reels-fill fs-2 opacity-50"></i>
                    </div>
                </div>

                <!-- Body -->
                <div class="ticket-body">
                    <div class="row g-3">
                        <div class="col-3">
                            <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($booking['poster']) ?>"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                                 alt="" style="width:100%;border-radius:8px;aspect-ratio:2/3;object-fit:cover">
                        </div>
                        <div class="col-9">
                            <h5 class="text-white fw-bold"><?= sanitize($booking['movie_title']) ?></h5>

                            <div class="row g-2 mt-1">
                                <div class="col-6">
                                    <div class="small text-muted-cb">Date</div>
                                    <div class="text-white small fw-semibold">
                                        <?= date('D, d M Y', strtotime($booking['show_date'])) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted-cb">Time</div>
                                    <div class="text-white small fw-semibold">
                                        <?= date('H:i', strtotime($booking['show_time'])) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted-cb">Hall</div>
                                    <div class="text-white small fw-semibold"><?= sanitize($booking['hall']) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted-cb">Seats</div>
                                    <div class="text-white small fw-semibold"><?= implode(', ', $seatCodes) ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted-cb">Total Seats</div>
                                    <div class="text-white small fw-semibold"><?= $booking['total_seats'] ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="small text-muted-cb">Status</div>
                                    <div><span class="status-confirmed">Confirmed</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="ticket-footer">
                    <div>
                        <div class="small text-muted-cb">Total Payment</div>
                        <div class="text-warning fw-bold fs-5"><?= formatPrice($booking['total_price']) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted-cb">Booked By</div>
                        <div class="text-white small fw-semibold"><?= sanitize($_SESSION['name']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex flex-column flex-sm-row gap-2 mt-4 justify-content-center">
                <a href="<?= BASE_URL ?>/user/my-tickets.php" class="btn btn-cb-primary">
                    <i class="bi bi-ticket-perforated me-2"></i>My Tickets
                </a>
                <a href="<?= BASE_URL ?>/movies.php" class="btn btn-outline-secondary">
                    <i class="bi bi-camera-reels me-2"></i>Browse Movies
                </a>
                <button onclick="window.print()" class="btn btn-outline-light">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
