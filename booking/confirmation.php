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
        <div class="col-lg-6 col-md-8">

            <!-- Success Banner -->
            <div class="text-center mb-5">
                <div class="d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:72px;height:72px;background:#F0FDF4;border-radius:50%;border:2px solid #BBF7D0">
                    <i class="bi bi-check-lg" style="font-size:2rem;color:var(--cb-success)"></i>
                </div>
                <h2 class="fw-bold mb-1" style="color:var(--cb-text)">Booking Confirmed!</h2>
                <p style="color:var(--cb-text-muted);font-size:.9rem">
                    Your tickets have been successfully booked. Enjoy the movie!
                </p>
            </div>

            <!-- Ticket Card -->
            <div class="cb-ticket">
                <!-- Ticket Header: Booking Code -->
                <div class="ticket-header">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;opacity:.75;margin-bottom:.25rem">Booking Code</div>
                            <div style="font-size:1.5rem;font-weight:800;font-family:monospace;letter-spacing:.05em">
                                <?= sanitize($booking['booking_code']) ?>
                            </div>
                        </div>
                        <div style="opacity:.4">
                            <i class="bi bi-camera-reels-fill" style="font-size:2rem"></i>
                        </div>
                    </div>
                </div>

                <!-- Ticket Body: Movie Details -->
                <div class="ticket-body">
                    <div class="row g-3 align-items-start">
                        <!-- Poster -->
                        <div class="col-auto">
                            <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($booking['poster']) ?>"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                                 alt="" style="width:72px;border-radius:var(--cb-radius-sm);aspect-ratio:2/3;object-fit:cover">
                        </div>
                        <!-- Info -->
                        <div class="col">
                            <h5 class="fw-bold mb-3" style="color:var(--cb-text);font-size:1rem">
                                <?= sanitize($booking['movie_title']) ?>
                            </h5>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted)">Date</div>
                                    <div style="font-size:.85rem;font-weight:600;color:var(--cb-text)">
                                        <?= date('D, d M Y', strtotime($booking['show_date'])) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted)">Time</div>
                                    <div style="font-size:.85rem;font-weight:600;color:var(--cb-text)">
                                        <?= date('H:i', strtotime($booking['show_time'])) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted)">Hall</div>
                                    <div style="font-size:.85rem;font-weight:600;color:var(--cb-text)">
                                        <?= sanitize($booking['hall']) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted)">Seats</div>
                                    <div style="font-size:.85rem;font-weight:600;color:var(--cb-text)">
                                        <?= implode(', ', $seatCodes) ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted)">Tickets</div>
                                    <div style="font-size:.85rem;font-weight:600;color:var(--cb-text)">
                                        <?= $booking['total_seats'] ?> seat<?= $booking['total_seats'] > 1 ? 's' : '' ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-text-muted)">Status</div>
                                    <div><span class="status-confirmed">Confirmed</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Footer: Payment -->
                <div class="ticket-footer">
                    <div>
                        <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-muted)">Total Payment</div>
                        <div style="font-size:1.35rem;font-weight:800;color:var(--cb-text)">
                            <?= formatPrice($booking['total_price']) ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--cb-muted)">Booked By</div>
                        <div style="font-size:.85rem;font-weight:600;color:var(--cb-text)">
                            <?= sanitize($_SESSION['name']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex flex-column flex-sm-row gap-2 mt-4 justify-content-center">
                <a href="<?= BASE_URL ?>/user/my-tickets.php" class="btn btn-cb-primary px-4">
                    <i class="bi bi-ticket-perforated me-2"></i>My Tickets
                </a>
                <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-secondary px-4">
                    <i class="bi bi-camera-reels me-2"></i>Browse More Movies
                </a>
                <button onclick="window.print()" class="btn btn-cb-secondary px-4">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
            </div>

            <!-- Info Note -->
            <div class="mt-4 p-3 text-center" style="background:var(--cb-section);border-radius:var(--cb-radius-sm);border:1px solid var(--cb-border)">
                <i class="bi bi-info-circle me-1" style="color:var(--cb-text)"></i>
                <span style="font-size:.82rem;color:var(--cb-text)">
                    Please bring your booking code or this page to the cinema for entry.
                </span>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
