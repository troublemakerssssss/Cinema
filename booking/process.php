<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

$showtime_id   = (int)($_POST['showtime_id']    ?? 0);
$selected_raw  = trim($_POST['selected_seats'] ?? '');

// Validate inputs
if ($showtime_id <= 0 || empty($selected_raw)) {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

// Parse and validate seat IDs (comma-separated integers)
$seat_ids = array_filter(array_map('intval', explode(',', $selected_raw)));
if (empty($seat_ids)) {
    header('Location: ' . BASE_URL . '/booking/seats.php?showtime_id=' . $showtime_id . '&error=no_seats');
    exit;
}

$db = getDB();

// Verify showtime exists
$stStmt = $db->prepare("SELECT * FROM showtimes WHERE id = ?");
$stStmt->execute([$showtime_id]);
$showtime = $stStmt->fetch();
if (!$showtime) {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

// Check all selected seats are available + belong to this showtime
$placeholders = implode(',', array_fill(0, count($seat_ids), '?'));
$seatCheck = $db->prepare("
    SELECT * FROM seats
    WHERE id IN ($placeholders) AND showtime_id = ? AND status = 'available'
");
$seatCheck->execute([...$seat_ids, $showtime_id]);
$validSeats = $seatCheck->fetchAll();

if (count($validSeats) !== count($seat_ids)) {
    // Some seats already taken
    header('Location: ' . BASE_URL . '/booking/seats.php?showtime_id=' . $showtime_id . '&error=seats_taken');
    exit;
}

// Begin transaction
$db->beginTransaction();
try {
    $totalPrice   = count($seat_ids) * $showtime['price'];
    $bookingCode  = generateBookingCode();
    $userId       = (int)$_SESSION['user_id'];

    // Insert booking
    $bookIns = $db->prepare("
        INSERT INTO bookings (user_id, showtime_id, booking_code, total_seats, total_price, status)
        VALUES (?,?,?,?,?,'confirmed')
    ");
    $bookIns->execute([$userId, $showtime_id, $bookingCode, count($seat_ids), $totalPrice]);
    $bookingId = (int)$db->lastInsertId();

    // Insert booking_details + mark seats as booked
    $detailIns = $db->prepare("INSERT INTO booking_details (booking_id, seat_id) VALUES (?,?)");
    $seatUpd   = $db->prepare("UPDATE seats SET status = 'booked' WHERE id = ?");
    foreach ($seat_ids as $sid) {
        $detailIns->execute([$bookingId, $sid]);
        $seatUpd->execute([$sid]);
    }

    $db->commit();

    header('Location: ' . BASE_URL . '/booking/confirmation.php?booking_id=' . $bookingId);
    exit;

} catch (Exception $e) {
    $db->rollBack();
    header('Location: ' . BASE_URL . '/booking/seats.php?showtime_id=' . $showtime_id . '&error=booking_failed');
    exit;
}
