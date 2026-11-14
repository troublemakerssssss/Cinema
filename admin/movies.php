<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db      = getDB();
$error   = '';
$success = '';

// --- ACTIONS ---
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM movies WHERE id = ?")->execute([$id]);
    header('Location: ' . BASE_URL . '/admin/movies.php?success=deleted');
    exit;
}

// Save (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']       ?? '');
    $genre    = trim($_POST['genre']       ?? '');
    $duration = (int)($_POST['duration']   ?? 0);
    $synopsis = trim($_POST['synopsis']    ?? '');
    $rating   = $_POST['rating']           ?? 'PG-13';
    $status   = $_POST['status']           ?? 'now_showing';
    $release  = $_POST['release_date']     ?? null;
    $poster   = trim($_POST['poster']      ?? 'default.jpg');

    if (empty($title) || empty($genre) || $duration <= 0) {
        $error = 'Title, genre and duration are required.';
    } else {
        $editId = (int)($_POST['edit_id'] ?? 0);
        if ($editId > 0) {
            $db->prepare("
                UPDATE movies SET title=?,genre=?,duration=?,synopsis=?,poster=?,rating=?,status=?,release_date=?
                WHERE id=?
            ")->execute([$title,$genre,$duration,$synopsis,$poster,$rating,$status,$release,$editId]);
            $success = 'Movie updated successfully.';
        } else {
            $db->prepare("
                INSERT INTO movies (title,genre,duration,synopsis,poster,rating,status,release_date)
                VALUES (?,?,?,?,?,?,?,?)
            ")->execute([$title,$genre,$duration,$synopsis,$poster,$rating,$status,$release]);
            $success = 'Movie added successfully.';
        }
        $action = 'list';
    }
}

// Fetch for edit
$editing = null;
if ($action === 'edit' && $id > 0) {
    $stmt    = $db->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
    if (!$editing) { $action = 'list'; }
}

// All movies
$movies = $db->query("SELECT * FROM movies ORDER BY created_at DESC")->fetchAll();

$success = $success ?: ($_GET['success'] === 'deleted' ? 'Movie deleted.' : '');

$page_title         = 'Manage Movies';
$current_admin_page = 'movies';
require_once __DIR__ . '/partials/admin-header.php';
?>
<script>var BASE_URL = '<?= BASE_URL ?>';</script>

<div class="d-flex" style="min-height:calc(100vh - 60px)">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-white fw-bold mb-0"><i class="bi bi-camera-reels me-2 text-warning"></i>Movies</h2>
            <button class="btn btn-cb-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#movieForm">
                <i class="bi bi-plus-circle me-1"></i><?= $editing ? 'Edit Movie' : 'Add Movie' ?>
            </button>
        </div>

        <?php if ($success): ?><div class="cb-alert-success mb-3" data-auto-dismiss="4000"><i class="bi bi-check-circle me-2"></i><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="cb-alert-danger  mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= sanitize($error) ?></div><?php endif; ?>

        <!-- Add/Edit Form -->
        <div class="collapse <?= ($editing || $error) ? 'show' : '' ?> mb-4" id="movieForm">
            <div class="cb-card">
                <div class="card-header"><?= $editing ? 'Edit Movie' : 'Add New Movie' ?></div>
                <div class="p-4">
                    <form method="POST">
                        <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="cb-label">Title *</label>
                                <input type="text" name="title" class="form-control cb-form-control"
                                       value="<?= sanitize($editing['title'] ?? ($_POST['title'] ?? '')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="cb-label">Genre *</label>
                                <input type="text" name="genre" class="form-control cb-form-control"
                                       placeholder="e.g. Action, Sci-Fi"
                                       value="<?= sanitize($editing['genre'] ?? ($_POST['genre'] ?? '')) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Duration (min) *</label>
                                <input type="number" name="duration" class="form-control cb-form-control" min="1"
                                       value="<?= (int)($editing['duration'] ?? ($_POST['duration'] ?? 90)) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Rating</label>
                                <select name="rating" class="form-select cb-form-control">
                                    <?php foreach (['G','PG','PG-13','R','NC-17'] as $r): ?>
                                    <option value="<?= $r ?>" <?= ($editing['rating'] ?? 'PG-13') === $r ? 'selected' : '' ?>><?= $r ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Status</label>
                                <select name="status" class="form-select cb-form-control">
                                    <option value="now_showing" <?= ($editing['status'] ?? '') === 'now_showing' ? 'selected' : '' ?>>Now Showing</option>
                                    <option value="coming_soon" <?= ($editing['status'] ?? '') === 'coming_soon' ? 'selected' : '' ?>>Coming Soon</option>
                                    <option value="ended"       <?= ($editing['status'] ?? '') === 'ended'       ? 'selected' : '' ?>>Ended</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Release Date</label>
                                <input type="date" name="release_date" class="form-control cb-form-control"
                                       value="<?= sanitize($editing['release_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="cb-label">Poster Filename</label>
                                <input type="text" name="poster" class="form-control cb-form-control"
                                       placeholder="e.g. movie.jpg"
                                       value="<?= sanitize($editing['poster'] ?? 'default.jpg') ?>">
                                <small class="text-muted-cb">Upload image to <code>assets/images/</code></small>
                            </div>
                            <div class="col-12">
                                <label class="cb-label">Synopsis</label>
                                <textarea name="synopsis" class="form-control cb-form-control" rows="3"><?= sanitize($editing['synopsis'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-cb-primary">
                                    <i class="bi bi-save me-1"></i><?= $editing ? 'Update Movie' : 'Save Movie' ?>
                                </button>
                                <?php if ($editing): ?>
                                <a href="<?= BASE_URL ?>/admin/movies.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Movies Table -->
        <div class="cb-card">
            <div class="card-header">All Movies (<?= count($movies) ?>)</div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead><tr>
                        <th>#</th><th>Poster</th><th>Title</th><th>Genre</th><th>Duration</th><th>Rating</th><th>Status</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                        <?php foreach ($movies as $m): ?>
                        <tr>
                            <td class="text-muted-cb"><?= $m['id'] ?></td>
                            <td>
                                <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($m['poster']) ?>"
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                                     style="width:40px;height:55px;object-fit:cover;border-radius:4px" alt="">
                            </td>
                            <td class="text-white fw-semibold"><?= sanitize($m['title']) ?></td>
                            <td class="small text-muted-cb"><?= sanitize($m['genre']) ?></td>
                            <td><?= $m['duration'] ?> min</td>
                            <td><span class="cb-badge-rating"><?= $m['rating'] ?></span></td>
                            <td>
                                <?php if ($m['status'] === 'now_showing'): ?>
                                    <span class="badge bg-success">Now Showing</span>
                                <?php elseif ($m['status'] === 'coming_soon'): ?>
                                    <span class="badge bg-secondary">Coming Soon</span>
                                <?php else: ?>
                                    <span class="badge bg-dark">Ended</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form id="del-<?= $m['id'] ?>" method="GET" style="display:inline"
                                      action="<?= BASE_URL ?>/admin/movies.php">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('del-<?= $m['id'] ?>', 'Delete <?= addslashes(sanitize($m['title'])) ?>?')">
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
