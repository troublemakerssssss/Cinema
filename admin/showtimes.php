<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db      = getDB();
$error   = '';
$success = '';

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM showtimes WHERE id = ?")->execute([$id]);
    header('Location: ' . BASE_URL . '/admin/showtimes.php?success=deleted');
    exit;
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id  = (int)($_POST['movie_id']   ?? 0);
    $show_date = trim($_POST['show_date']   ?? '');
    $show_time = trim($_POST['show_time']   ?? '');
    $hall      = trim($_POST['hall']        ?? '');
    $price     = (float)($_POST['price']    ?? 0);
    $editId    = (int)($_POST['edit_id']    ?? 0);

    if (!$movie_id || empty($show_date) || empty($show_time) || empty($hall) || $price <= 0) {
        $error = 'All fields are required.';
    } else {
        // Check duplicate
        $checkStmt = $db->prepare("
            SELECT id FROM showtimes 
            WHERE movie_id = ? AND show_date = ? AND TIME_FORMAT(show_time, '%H:%i') = TIME_FORMAT(?, '%H:%i') AND hall = ? AND id != ?
            LIMIT 1
        ");
        $checkStmt->execute([$movie_id, $show_date, $show_time, $hall, $editId]);
        if ($checkStmt->fetch()) {
            $error = 'Showtime already exists.';
        } else {
            if ($editId > 0) {
                $db->prepare("
                    UPDATE showtimes SET movie_id=?,show_date=?,show_time=?,hall=?,price=? WHERE id=?
                ")->execute([$movie_id,$show_date,$show_time,$hall,$price,$editId]);
                header('Location: ' . BASE_URL . '/admin/showtimes.php?success=updated');
                exit;
            } else {
                $db->prepare("
                    INSERT INTO showtimes (movie_id,show_date,show_time,hall,price)
                    VALUES (?,?,?,?,?)
                ")->execute([$movie_id,$show_date,$show_time,$hall,$price]);
                header('Location: ' . BASE_URL . '/admin/showtimes.php?success=added');
                exit;
            }
        }
    }
}

$editing = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM showtimes WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
    if (!$editing) $action = 'list';
} elseif (!empty($editId)) {
    // Validation failed during POST: preserve edit mode
    $stmt = $db->prepare("SELECT * FROM showtimes WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
    if (!$editing) {
        $editing = ['id' => $editId];
    }
}

// All movies (for dropdown)
$movieList = $db->query("SELECT id, title FROM movies WHERE status='now_showing' OR status='coming_soon' ORDER BY title")->fetchAll();

// All showtimes with join
$showtimes = $db->query("
    SELECT s.*, m.title AS movie_title
    FROM showtimes s JOIN movies m ON m.id = s.movie_id
    ORDER BY s.show_date DESC, s.show_time DESC
")->fetchAll();

$successKey = $_GET['success'] ?? '';
if ($successKey === 'added' || $successKey === 'saved') {
    $success = 'Showtime added.';
} elseif ($successKey === 'updated') {
    $success = 'Showtime updated.';
} elseif ($successKey === 'deleted') {
    $success = 'Showtime deleted.';
}

$page_title         = 'Manage Showtimes';
$current_admin_page = 'showtimes';
require_once __DIR__ . '/partials/admin-header.php';
?>

<div class="cb-admin-layout">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <main class="cb-admin-main">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color:var(--cb-text)">
                    <i class="bi bi-calendar-week me-2" style="color:var(--cb-text)"></i>Showtimes
                </h2>
                <p class="text-muted-cb mb-0" style="font-size:.875rem"><?= count($showtimes) ?> total showtimes</p>
            </div>
            <button class="btn btn-cb-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#stForm">
                <i class="bi bi-plus-circle me-1"></i><?= $editing ? 'Edit Showtime' : 'Add Showtime' ?>
            </button>
        </div>

        <?php if ($success): ?>
        <div class="cb-alert-success mb-3" data-auto-dismiss="4000">
            <i class="bi bi-check-circle-fill"></i><?= sanitize($success) ?>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="cb-alert-danger mb-3">
            <i class="bi bi-exclamation-circle-fill"></i><?= sanitize($error) ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="collapse <?= ($editing || $error) ? 'show' : '' ?> mb-4" id="stForm">
            <div class="cb-card">
                <div class="card-header">
                    <i class="bi bi-<?= $editing ? 'pencil' : 'plus-circle' ?> me-2" style="color:var(--cb-text)"></i>
                    <?= $editing ? 'Edit Showtime' : 'Add Showtime' ?>
                </div>
                <div class="p-4">
                    <form method="POST" action="<?= BASE_URL ?>/admin/showtimes.php">
                        <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="cb-label">Movie *</label>
                                <select name="movie_id" class="form-select cb-form-control" required>
                                    <option value="">— Select Movie —</option>
                                    <?php
                                    $selectedMovieId = (int)($_POST['movie_id'] ?? ($editing['movie_id'] ?? 0));
                                    foreach ($movieList as $mv): ?>
                                    <option value="<?= $mv['id'] ?>"
                                        <?= $selectedMovieId == $mv['id'] ? 'selected' : '' ?>>
                                        <?= sanitize($mv['title']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Date *</label>
                                <input type="date" name="show_date" class="form-control cb-form-control"
                                       value="<?= sanitize($_POST['show_date'] ?? ($editing['show_date'] ?? '')) ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="cb-label">Time *</label>
                                <?php
                                $timeVal = $_POST['show_time'] ?? ($editing['show_time'] ?? '');
                                if (!empty($timeVal)) {
                                    $timeVal = date('H:i', strtotime($timeVal));
                                }
                                ?>
                                <input type="time" name="show_time" class="form-control cb-form-control"
                                       value="<?= sanitize($timeVal) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Hall *</label>
                                <select name="hall" class="form-select cb-form-control" required>
                                    <?php
                                    $selectedHall = $_POST['hall'] ?? ($editing['hall'] ?? '');
                                    foreach (['Hall 1','Hall 2','Hall 3','Hall 4','Hall 5'] as $h): ?>
                                    <option value="<?= $h ?>" <?= $selectedHall === $h ? 'selected' : '' ?>><?= $h ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Ticket Price (Rp) *</label>
                                <input type="number" name="price" class="form-control cb-form-control"
                                       min="1000" step="1000"
                                       value="<?= (int)($_POST['price'] ?? ($editing['price'] ?? 55000)) ?>" required>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-cb-primary">
                                    <i class="bi bi-save me-1"></i><?= $editing ? 'Update' : 'Save' ?>
                                </button>
                                <?php if ($editing): ?>
                                <a href="<?= BASE_URL ?>/admin/showtimes.php" class="btn btn-cb-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="cb-card">
            <div class="card-header">
                <strong>All Showtimes</strong>
                <span class="text-muted-cb ms-1" style="font-size:.8rem">(<?= count($showtimes) ?>)</span>
            </div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th><th>Movie</th><th>Date</th><th>Time</th><th>Hall</th><th>Price</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($showtimes)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted-cb">No showtimes found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($showtimes as $st): ?>
                        <tr>
                            <td style="color:var(--cb-text-muted);font-size:.82rem"><?= $st['id'] ?></td>
                            <td style="font-weight:600"><?= sanitize($st['movie_title']) ?></td>
                            <td><?= date('d M Y', strtotime($st['show_date'])) ?></td>
                            <td style="font-weight:600"><?= date('H:i', strtotime($st['show_time'])) ?></td>
                            <td><?= sanitize($st['hall']) ?></td>
                            <td style="font-weight:700;color:var(--cb-text)"><?= formatPrice($st['price']) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="?action=edit&id=<?= $st['id'] ?>" class="btn btn-cb-secondary btn-sm py-1 px-2">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form id="del-st-<?= $st['id'] ?>" method="GET" style="display:inline"
                                          action="<?= BASE_URL ?>/admin/showtimes.php">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                        <button type="button" class="btn btn-sm py-1 px-2" style="background:#FEF2F2;border:1px solid #FECACA;color:var(--cb-danger)"
                                                onclick="confirmDelete('del-st-<?= $st['id'] ?>', 'Delete this showtime?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/admin-footer.php'; ?>
