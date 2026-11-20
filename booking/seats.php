<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$showtime_id = (int)($_GET['showtime_id'] ?? 0);
if ($showtime_id <= 0) {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

$db = getDB();

// Fetch showtime + movie
$stmt = $db->prepare("
    SELECT s.*, m.title AS movie_title, m.poster, m.rating, m.duration
    FROM showtimes s
    JOIN movies m ON m.id = s.movie_id
    WHERE s.id = ?
");
$stmt->execute([$showtime_id]);
$showtime = $stmt->fetch();

if (!$showtime) {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

// Generate seats if they don't exist for this showtime
$existing = $db->prepare("SELECT COUNT(*) FROM seats WHERE showtime_id = ?");
$existing->execute([$showtime_id]);
if ((int)$existing->fetchColumn() === 0) {
    // Create a 8×10 grid (rows A-H, seats 1-10)
    $ins = $db->prepare("INSERT INTO seats (showtime_id, seat_row, seat_number, status) VALUES (?,?,?,'available')");
    foreach (range('A','H') as $row) {
        for ($num = 1; $num <= 10; $num++) {
            $ins->execute([$showtime_id, $row, $num]);
        }
    }
}

// Fetch all seats
$seatStmt = $db->prepare("
    SELECT * FROM seats WHERE showtime_id = ? ORDER BY seat_row ASC, seat_number ASC
");
$seatStmt->execute([$showtime_id]);
$seats = $seatStmt->fetchAll();

// Group by row
$seatGrid = [];
foreach ($seats as $seat) {
    $seatGrid[$seat['seat_row']][] = $seat;
}

// Count available seats
$availableCount = count(array_filter($seats, fn($s) => $s['status'] === 'available'));

$page_title   = 'Select Seats — ' . sanitize($showtime['movie_title']);
$current_page = 'movies';
require_once __DIR__ . '/../includes/header.php';
?>
<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<!-- Breadcrumb -->
<div style="background:var(--cb-white);border-bottom:1px solid var(--cb-border)">
    <div class="container py-3">
        <nav style="font-size:.82rem;" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider: '›';">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/movies.php" style="color:var(--cb-text-muted)">Movies</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $showtime['movie_id'] ?>" style="color:var(--cb-text-muted)"><?= sanitize($showtime['movie_title']) ?></a></li>
                <li class="breadcrumb-item active" style="color:var(--cb-text)">Select Seats</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <?php
    $error = $_GET['error'] ?? '';
    if ($error === 'seats_taken'): ?>
    <div class="cb-alert-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Some of your selected seats were just booked by someone else. Please select new seats.
    </div>
    <?php elseif ($error === 'booking_failed'): ?>
    <div class="cb-alert-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Booking failed due to an error. Please try again.
    </div>
    <?php endif; ?>

    <!-- Page Header (Reference 3: Royal Cinema) -->
    <div class="text-center mb-4">
        <h2 class="fw-bold mb-1" style="color:var(--cb-text);letter-spacing:-0.02em">Choose your seats</h2>
        <p class="text-muted-cb mb-0" style="font-size:.9rem"><?= sanitize($showtime['movie_title']) ?> &bull; <?= sanitize($showtime['hall']) ?> &bull; <?= formatTime($showtime['show_time']) ?></p>
    </div>

    <div class="row g-4">
        <!-- ========================
             LEFT: SEAT MAP
             ======================== -->
        <div class="col-lg-8">
            <div class="cb-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span>
                        <i class="bi bi-grid-3x3-gap me-2" style="color:var(--cb-text)"></i>
                        <strong>Seat Layout</strong>
                    </span>
                    <span style="font-size:.8rem;color:var(--cb-muted)">
                        <i class="bi bi-check-circle-fill me-1" style="color:var(--cb-success)"></i><?= $availableCount ?> seats available
                    </span>
                </div>
                <div class="p-4">
                    <!-- Screen indicator (Reference 3: Curved Screen) -->
                    <div class="seat-screen-wrapper mb-5">
                        <div class="seat-screen"></div>
                        <div class="seat-screen-label">SCREEN</div>
                    </div>

                    <!-- Seat Map -->
                    <div class="seat-map-container">
                        <?php foreach ($seatGrid as $rowLabel => $rowSeats): ?>
                        <div class="seat-row">
                            <div class="seat-row-label"><?= $rowLabel ?></div>
                            <?php foreach ($rowSeats as $i => $seat): ?>
                                <?php
                                $status = $seat['status'];
                                $code   = $rowLabel . $seat['seat_number'];
                                ?>
                                <div class="seat <?= $status ?>"
                                     data-seat-id="<?= $seat['id'] ?>"
                                     data-seat-code="<?= $code ?>"
                                     title="Seat <?= $code ?> — <?= ucfirst($status) ?>">
                                    <?= $seat['seat_number'] ?>
                                </div>
                                <!-- Aisle gap after seat 5 -->
                                <?php if ($i === 4): ?>
                                    <div class="seat-aisle"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <div class="seat-row-label ms-1"><?= $rowLabel ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Legend (Reference 3: Available, Selected, Unavailable) -->
                    <div class="seat-legend mt-4 pt-3" style="border-top:1px solid var(--cb-border)">
                        <div class="seat-legend-item">
                            <div class="seat-legend-box available"></div>
                            <span>Available</span>
                        </div>
                        <div class="seat-legend-item">
                            <div class="seat-legend-box selected"></div>
                            <span>Selected</span>
                        </div>
                        <div class="seat-legend-item">
                            <div class="seat-legend-box booked"></div>
                            <span>Unavailable</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================
             RIGHT: BOOKING SUMMARY (Reference 3)
             ======================== -->
        <div class="col-lg-4">
            <div class="cb-card" style="position:sticky;top:84px">
                <div class="card-header">
                    <i class="bi bi-receipt me-2" style="color:var(--cb-text)"></i>
                    <strong>Your Summary</strong>
                </div>
                <div class="p-4">
                    <!-- Movie Info -->
                    <div class="d-flex gap-3 mb-4 pb-3" style="border-bottom:1px solid var(--cb-border)">
                        <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($showtime['poster']) ?>"
                             onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                             alt="" style="width:60px;height:88px;object-fit:cover;border-radius:var(--cb-radius-sm);flex-shrink:0">
                        <div class="min-w-0">
                            <div style="font-weight:800;font-size:.95rem;color:var(--cb-text);line-height:1.3" class="mb-1">
                                <?= sanitize($showtime['movie_title']) ?>
                            </div>
                            <div style="font-size:.8rem;color:var(--cb-muted)" class="d-flex align-items-center gap-1 mb-1">
                                <i class="bi bi-calendar3"></i>
                                <?= formatDate($showtime['show_date']) ?>
                            </div>
                            <div style="font-size:.8rem;color:var(--cb-muted)" class="d-flex align-items-center gap-1 mb-1">
                                <i class="bi bi-clock"></i>
                                <?= formatTime($showtime['show_time']) ?>
                            </div>
                            <div style="font-size:.8rem;color:var(--cb-muted)" class="d-flex align-items-center gap-1">
                                <i class="bi bi-building"></i>
                                <?= sanitize($showtime['hall']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden price field -->
                    <input type="hidden" id="seat-price" value="<?= $showtime['price'] ?>">

                    <!-- Summary Rows -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:.85rem;color:var(--cb-muted)">Seats Selected</span>
                            <span style="font-weight:700;color:var(--cb-text)" id="selected-count">0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span style="font-size:.85rem;color:var(--cb-muted)">Your Seats</span>
                            <span style="font-size:.85rem;font-weight:700;color:var(--cb-text);text-align:right;max-width:60%" id="selected-seats-label">—</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:.85rem;color:var(--cb-muted)">Price / Seat</span>
                            <span style="font-size:.85rem;color:var(--cb-text)"><?= formatPrice($showtime['price']) ?></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3" style="border-top:1px solid var(--cb-border)">
                        <span style="font-weight:700;color:var(--cb-text)">Total Price</span>
                        <span style="font-weight:800;font-size:1.35rem;color:var(--cb-text)" id="selected-total">Rp 0</span>
                    </div>

                    <!-- Booking form -->
                    <form method="POST" action="<?= BASE_URL ?>/booking/process.php" id="booking-form" class="mt-4">
                        <input type="hidden" name="showtime_id"    value="<?= $showtime_id ?>">
                        <input type="hidden" name="selected_seats" id="selected-seat-ids" value="">

                        <button type="submit" class="btn btn-cb-primary w-100 py-3 fw-semibold"
                                id="proceed-btn" disabled>
                            Continue to Ticket
                        </button>
                    </form>

                    <p style="font-size:.75rem;color:var(--cb-muted);text-align:center;margin-top:.75rem">
                        <i class="bi bi-shield-check me-1"></i>Secure instant reservation
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
