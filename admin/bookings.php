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

<div class="cb-admin-layout">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <main class="cb-admin-main">
        <!-- Page Header -->
        <div class="mb-4">
            <h2 class="fw-bold mb-1" style="color:var(--cb-text)">
                <i class="bi bi-ticket-perforated me-2" style="color:var(--cb-text)"></i>Bookings
            </h2>
            <p class="text-muted-cb mb-0" style="font-size:.875rem"><?= $total ?> booking<?= $total !== 1 ? 's' : '' ?> found</p>
        </div>

        <?php if ($success): ?>
        <div class="cb-alert-success mb-3" data-auto-dismiss="4000">
            <i class="bi bi-check-circle-fill"></i><?= sanitize($success) ?>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="cb-card mb-4">
            <div class="p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="cb-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text cb-ig-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control cb-form-control"
                                   placeholder="Search code, user, movie..." value="<?= sanitize($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="cb-label">Status</label>
                        <select name="status" class="form-select cb-form-control">
                            <option value="">All Status</option>
                            <option value="confirmed" <?= $fStatus==='confirmed' ? 'selected':'' ?>>Confirmed</option>
                            <option value="pending"   <?= $fStatus==='pending'   ? 'selected':'' ?>>Pending</option>
                            <option value="cancelled" <?= $fStatus==='cancelled' ? 'selected':'' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-cb-primary w-100">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= BASE_URL ?>/admin/bookings.php" class="btn btn-cb-secondary w-100">
                            <i class="bi bi-x-lg me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="cb-card">
            <div class="card-header">
                <strong>All Bookings</strong>
                <span class="text-muted-cb ms-1" style="font-size:.8rem">(<?= $total ?>)</span>
            </div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>User</th>
                            <th>Movie</th>
                            <th>Show</th>
                            <th>Seats</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted-cb">No bookings found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <span style="font-family:monospace;font-size:.82rem;font-weight:700;color:var(--cb-text)">
                                    <?= sanitize($b['booking_code']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight:600;font-size:.875rem"><?= sanitize($b['user_name']) ?></div>
                                <div style="font-size:.72rem;color:var(--cb-muted)"><?= sanitize($b['user_email']) ?></div>
                            </td>
                            <td style="font-size:.85rem;max-width:140px">
                                <span class="d-block" style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    <?= sanitize($b['movie_title']) ?>
                                </span>
                            </td>
                            <td style="font-size:.82rem;color:var(--cb-muted)">
                                <?= date('d M', strtotime($b['show_date'])) ?><br>
                                <span style="color:var(--cb-text);font-weight:600"><?= date('H:i', strtotime($b['show_time'])) ?></span>
                                — <?= sanitize($b['hall']) ?>
                            </td>
                            <td style="text-align:center;font-weight:700"><?= $b['total_seats'] ?></td>
                            <td style="font-weight:700;color:var(--cb-text)"><?= formatPrice($b['total_price']) ?></td>
                            <td><span class="status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                            <td>
                                <form method="POST" class="d-inline-flex gap-1 align-items-center">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <select name="new_status" class="form-select cb-form-control py-1" style="font-size:.75rem;width:110px">
                                        <option value="confirmed" <?= $b['status']==='confirmed'?'selected':''?>>Confirmed</option>
                                        <option value="pending"   <?= $b['status']==='pending'  ?'selected':''?>>Pending</option>
                                        <option value="cancelled" <?= $b['status']==='cancelled'?'selected':''?>>Cancelled</option>
                                    </select>
                                    <button class="btn btn-cb-secondary btn-sm py-1 px-2">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="p-3 d-flex justify-content-end">
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p===$page?'active':'' ?>">
                            <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($fStatus) ?>"><?= $p ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/admin-footer.php'; ?>
