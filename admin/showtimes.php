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
    $show_date = $_POST['show_date']          ?? '';
    $show_time = $_POST['show_time']          ?? '';
    $hall      = trim($_POST['hall']          ?? '');
    $price     = (float)($_POST['price']     ?? 0);

    if (!$movie_id || empty($show_date) || empty($show_time) || empty($hall) || $price <= 0) {
        $error = 'All fields are required.';
    } else {
        $editId = (int)($_POST['edit_id'] ?? 0);
        if ($editId > 0) {
            $db->prepare("
                UPDATE showtimes SET movie_id=?,show_date=?,show_time=?,hall=?,price=? WHERE id=?
            ")->execute([$movie_id,$show_date,$show_time,$hall,$price,$editId]);
            $success = 'Showtime updated.';
        } else {
            $db->prepare("
                INSERT INTO showtimes (movie_id,show_date,show_time,hall,price)
                VALUES (?,?,?,?,?)
            ")->execute([$movie_id,$show_date,$show_time,$hall,$price]);
            $success = 'Showtime added.';
        }
        $action = 'list';
    }
}

$editing = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM showtimes WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
    if (!$editing) $action = 'list';
}

// All movies (for dropdown)
$movieList = $db->query("SELECT id, title FROM movies WHERE status='now_showing' OR status='coming_soon' ORDER BY title")->fetchAll();

// All showtimes with join
$showtimes = $db->query("
    SELECT s.*, m.title AS movie_title
    FROM showtimes s JOIN movies m ON m.id = s.movie_id
    ORDER BY s.show_date DESC, s.show_time DESC
")->fetchAll();

$success = $success ?: (isset($_GET['success']) ? 'Showtime deleted.' : '');

$page_title         = 'Manage Showtimes';
$current_admin_page = 'showtimes';
require_once __DIR__ . '/partials/admin-header.php';
?>

<div class="d-flex" style="min-height:calc(100vh - 60px)">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-white fw-bold mb-0"><i class="bi bi-calendar-week me-2 text-warning"></i>Showtimes</h2>
            <button class="btn btn-cb-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#stForm">
                <i class="bi bi-plus-circle me-1"></i>Add Showtime
            </button>
        </div>

        <?php if ($success): ?><div class="cb-alert-success mb-3" data-auto-dismiss="4000"><i class="bi bi-check-circle me-2"></i><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="cb-alert-danger  mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= sanitize($error) ?></div><?php endif; ?>

        <!-- Form -->
        <div class="collapse <?= ($editing || $error) ? 'show' : '' ?> mb-4" id="stForm">
            <div class="cb-card">
                <div class="card-header"><?= $editing ? 'Edit Showtime' : 'Add Showtime' ?></div>
                <div class="p-4">
                    <form method="POST">
                        <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="cb-label">Movie *</label>
                                <select name="movie_id" class="form-select cb-form-control" required>
                                    <option value="">— Select Movie —</option>
                                    <?php foreach ($movieList as $mv): ?>
                                    <option value="<?= $mv['id'] ?>"
                                        <?= ($editing['movie_id'] ?? 0) == $mv['id'] ? 'selected' : '' ?>>
                                        <?= sanitize($mv['title']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Date *</label>
                                <input type="date" name="show_date" class="form-control cb-form-control"
                                       value="<?= sanitize($editing['show_date'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="cb-label">Time *</label>
                                <input type="time" name="show_time" class="form-control cb-form-control"
                                       value="<?= sanitize($editing['show_time'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Hall *</label>
                                <select name="hall" class="form-select cb-form-control" required>
                                    <?php foreach (['Hall 1','Hall 2','Hall 3','Hall 4','Hall 5'] as $h): ?>
                                    <option value="<?= $h ?>" <?= ($editing['hall'] ?? '') === $h ? 'selected' : '' ?>><?= $h ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Ticket Price (Rp) *</label>
                                <input type="number" name="price" class="form-control cb-form-control"
                                       min="1000" step="1000"
                                       value="<?= (int)($editing['price'] ?? 55000) ?>" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-cb-primary">
                                    <i class="bi bi-save me-1"></i><?= $editing ? 'Update' : 'Save' ?>
                                </button>
                                <?php if ($editing): ?>
                                <a href="<?= BASE_URL ?>/admin/showtimes.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="cb-card">
            <div class="card-header">All Showtimes (<?= count($showtimes) ?>)</div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead><tr>
                        <th>#</th><th>Movie</th><th>Date</th><th>Time</th><th>Hall</th><th>Price</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($showtimes as $st): ?>
                        <tr>
                            <td class="text-muted-cb"><?= $st['id'] ?></td>
                            <td class="text-white"><?= sanitize($st['movie_title']) ?></td>
                            <td><?= date('d M Y', strtotime($st['show_date'])) ?></td>
                            <td><?= date('H:i', strtotime($st['show_time'])) ?></td>
                            <td><?= sanitize($st['hall']) ?></td>
                            <td class="text-warning"><?= formatPrice($st['price']) ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $st['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form id="del-st-<?= $st['id'] ?>" method="GET" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $st['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('del-st-<?= $st['id'] ?>', 'Delete this showtime?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/partials/admin-footer.php'; ?>
