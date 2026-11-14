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

$page_title   = 'Select Seats — ' . sanitize($showtime['movie_title']);
$current_page = 'movies';
require_once __DIR__ . '/../includes/header.php';
?>
<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<div class="container py-5">
    <!-- Back -->
    <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $showtime['movie_id'] ?>"
       class="btn btn-sm btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>

    <div class="row g-4">
        <!-- Seat Map -->
        <div class="col-lg-8">
            <div class="cb-card">
                <div class="card-header">
                    <i class="bi bi-grid-3x3-gap me-2 text-warning"></i>Select Your Seats
                </div>
                <div class="p-4">
                    <!-- Screen -->
                    <div class="seat-screen-label">SCREEN</div>
                    <div class="seat-screen"></div>

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
                                     title="<?= $code ?> — <?= ucfirst($status) ?>">
                                    <?= $seat['seat_number'] ?>
                                </div>
                                <!-- Aisle gap after seat 5 -->
                                <?php if ($i === 4): ?>
                                    <div class="seat-aisle"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Legend -->
                    <div class="seat-legend">
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
                            <span>Booked</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Summary -->
        <div class="col-lg-4">
            <div class="cb-card sticky-top" style="top:80px">
                <div class="card-header">
                    <i class="bi bi-receipt me-2 text-warning"></i>Booking Summary
                </div>
                <div class="p-4">
                    <!-- Movie info -->
                    <div class="d-flex gap-3 mb-3 pb-3 border-bottom border-cb">
                        <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($showtime['poster']) ?>"
                             onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                             alt="" style="width:60px;height:90px;object-fit:cover;border-radius:6px">
                        <div>
                            <div class="text-white fw-semibold"><?= sanitize($showtime['movie_title']) ?></div>
                            <div class="small text-muted-cb mt-1">
                                <i class="bi bi-calendar me-1"></i><?= formatDate($showtime['show_date']) ?>
                            </div>
                            <div class="small text-muted-cb">
                                <i class="bi bi-clock me-1"></i><?= formatTime($showtime['show_time']) ?>
                            </div>
                            <div class="small text-muted-cb">
                                <i class="bi bi-building me-1"></i><?= sanitize($showtime['hall']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden fields -->
                    <input type="hidden" id="seat-price" value="<?= $showtime['price'] ?>">

                    <!-- Summary -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-cb small">Seats Selected</span>
                            <span class="text-white fw-semibold" id="selected-count">0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-cb small">Seat Numbers</span>
                            <span class="text-white small text-end" id="selected-seats-label">—</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-cb small">Price / Seat</span>
                            <span class="text-white small"><?= formatPrice($showtime['price']) ?></span>
                        </div>
                        <hr class="border-cb">
                        <div class="d-flex justify-content-between">
                            <span class="text-white fw-semibold">Total</span>
                            <span class="text-warning fw-bold fs-5" id="selected-total">Rp 0</span>
                        </div>
                    </div>

                    <!-- Booking form -->
                    <form method="POST" action="<?= BASE_URL ?>/booking/process.php" id="booking-form">
                        <input type="hidden" name="showtime_id"    value="<?= $showtime_id ?>">
                        <input type="hidden" name="selected_seats" id="selected-seat-ids" value="">

                        <button type="submit" class="btn btn-cb-warning w-100 py-2 fw-bold"
                                id="proceed-btn" disabled>
                            Select Seats to Continue
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
