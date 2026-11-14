<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$movie_id = (int)($_GET['movie_id'] ?? 0);
$date     = $_GET['date'] ?? '';

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date']);
    exit;
}

if ($movie_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid movie']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("
    SELECT id, show_date, show_time, hall, price
    FROM showtimes
    WHERE movie_id = ? AND show_date = ?
    ORDER BY show_time ASC
");
$stmt->execute([$movie_id, $date]);
$showtimes = $stmt->fetchAll();

// Format time for display
foreach ($showtimes as &$st) {
    $st['show_time'] = date('H:i', strtotime($st['show_time']));
}

echo json_encode(['success' => true, 'showtimes' => $showtimes]);
