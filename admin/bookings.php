<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db      = getDB();
$success = '';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bid    = (int)$_POST['booking_id'];
    $status = in_array($_POST['new_status'], ['confirmed','pending','cancelled'])
              ? $_POST['new_status'] : 'confirmed';
    $db->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$status, $bid]);
    $success = 'Booking status updated.';
}

// Filters
$search    = trim($_GET['search'] ?? '');
$fStatus   = $_GET['status'] ?? '';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 15;
$offset    = ($page - 1) * $perPage;

$params = [];
$where  = "WHERE 1=1";
if ($search !== '') {
    $where   .= " AND (b.booking_code LIKE ? OR u.name LIKE ? OR m.title LIKE ?)";
    $params   = array_merge($params, ["%$search%","%$search%","%$search%"]);
}
if ($fStatus !== '') {
    $where   .= " AND b.status = ?";
    $params[] = $fStatus;
}

$countStmt = $db->prepare("
    SELECT COUNT(*)
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m ON m.id = s.movie_id
    $where
");
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

$stmt = $db->prepare("
    SELECT b.id, b.booking_code, b.total_seats, b.total_price, b.status, b.created_at,
           u.name AS user_name, u.email AS user_email,
           m.title AS movie_title,
           s.show_date, s.show_time, s.hall
    FROM bookings b
    JOIN users u ON u.id = b.user_id
    JOIN showtimes s ON s.id = b.showtime_id
    JOIN movies m ON m.id = s.movie_id
    $where
    ORDER BY b.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([...$params, $perPage, $offset]);
$bookings = $stmt->fetchAll();

$page_title         = 'Manage Bookings';
$current_admin_page = 'bookings';
require_once __DIR__ . '/partials/admin-header.php';
?>

<div class="d-flex" style="min-height:calc(100vh - 60px)">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <main class="flex-grow-1 p-4">
        <h2 class="text-white fw-bold mb-4"><i class="bi bi-ticket-perforated me-2 text-warning"></i>Bookings</h2>

        <?php if ($success): ?><div class="cb-alert-success mb-3" data-auto-dismiss="4000"><i class="bi bi-check-circle me-2"></i><?= sanitize($success) ?></div><?php endif; ?>

        <!-- Filters -->
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control cb-form-control"
                       placeholder="Search code, user, movie..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select cb-form-control">
                    <option value="">All Status</option>
                    <option value="confirmed" <?= $fStatus==='confirmed' ? 'selected':'' ?>>Confirmed</option>
                    <option value="pending"   <?= $fStatus==='pending'   ? 'selected':'' ?>>Pending</option>
                    <option value="cancelled" <?= $fStatus==='cancelled' ? 'selected':'' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-cb-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>/admin/bookings.php" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>

        <!-- Table -->
        <div class="cb-card">
            <div class="card-header">Total: <?= $total ?> booking(s)</div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead><tr>
                        <th>Code</th><th>User</th><th>Movie</th><th>Show</th><th>Seats</th><th>Total</th><th>Status</th><th>Action</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td class="font-monospace text-warning small"><?= sanitize($b['booking_code']) ?></td>
                            <td>
                                <div class="text-white small fw-semibold"><?= sanitize($b['user_name']) ?></div>
                                <div class="text-muted-cb" style="font-size:.7rem"><?= sanitize($b['user_email']) ?></div>
                            </td>
                            <td class="text-white small"><?= sanitize($b['movie_title']) ?></td>
                            <td class="small text-muted-cb">
                                <?= date('d M', strtotime($b['show_date'])) ?><br>
                                <?= date('H:i', strtotime($b['show_time'])) ?> — <?= sanitize($b['hall']) ?>
                            </td>
                            <td class="text-center"><?= $b['total_seats'] ?></td>
                            <td class="text-warning"><?= formatPrice($b['total_price']) ?></td>
                            <td><span class="status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            <td>
                                <form method="POST" class="d-inline-flex gap-1 align-items-center">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <select name="new_status" class="form-select cb-form-control py-1" style="font-size:.75rem;width:110px">
                                        <option value="confirmed" <?= $b['status']==='confirmed'?'selected':''?>>Confirmed</option>
                                        <option value="pending"   <?= $b['status']==='pending'  ?'selected':''?>>Pending</option>
                                        <option value="cancelled" <?= $b['status']==='cancelled'?'selected':''?>>Cancelled</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary py-1 px-2">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="p-3">
                <nav><ul class="pagination pagination-sm mb-0 justify-content-end">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p===$page?'active':'' ?>">
                        <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($fStatus) ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/admin-footer.php'; ?>
