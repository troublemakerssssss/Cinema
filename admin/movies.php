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
    $release  = !empty($_POST['release_date']) ? trim($_POST['release_date']) : null;
    $poster   = trim($_POST['poster']      ?? 'default.jpg');
    $editId   = (int)($_POST['edit_id']    ?? 0);

    if (empty($title) || empty($genre) || $duration <= 0) {
        $error = 'Title, genre and duration are required.';
    } else {
        // Check duplicate
        $checkStmt = $db->prepare("
            SELECT id FROM movies 
            WHERE LOWER(title) = LOWER(?) AND (release_date <=> ?) AND id != ?
            LIMIT 1
        ");
        $checkStmt->execute([$title, $release, $editId]);
        if ($checkStmt->fetch()) {
            $error = 'Movie already exists.';
        } else {
            if ($editId > 0) {
                $db->prepare("
                    UPDATE movies SET title=?,genre=?,duration=?,synopsis=?,poster=?,rating=?,status=?,release_date=?
                    WHERE id=?
                ")->execute([$title,$genre,$duration,$synopsis,$poster,$rating,$status,$release,$editId]);
                header('Location: ' . BASE_URL . '/admin/movies.php?success=updated');
                exit;
            } else {
                $db->prepare("
                    INSERT INTO movies (title,genre,duration,synopsis,poster,rating,status,release_date)
                    VALUES (?,?,?,?,?,?,?,?)
                ")->execute([$title,$genre,$duration,$synopsis,$poster,$rating,$status,$release]);
                header('Location: ' . BASE_URL . '/admin/movies.php?success=added');
                exit;
            }
        }
    }
}

// Fetch for edit
$editing = null;
if ($action === 'edit' && $id > 0) {
    $stmt    = $db->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
    if (!$editing) { $action = 'list'; }
} elseif (!empty($editId)) {
    // Validation failed during POST: preserve edit mode
    $stmt    = $db->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
    if (!$editing) {
        $editing = ['id' => $editId];
    }
}

// All movies
$movies = $db->query("SELECT * FROM movies ORDER BY created_at DESC")->fetchAll();

$successKey = $_GET['success'] ?? '';
if ($successKey === 'added' || $successKey === 'saved') {
    $success = 'Movie added successfully.';
} elseif ($successKey === 'updated') {
    $success = 'Movie updated successfully.';
} elseif ($successKey === 'deleted') {
    $success = 'Movie deleted.';
}

$page_title         = 'Manage Movies';
$current_admin_page = 'movies';
require_once __DIR__ . '/partials/admin-header.php';
?>

<div class="cb-admin-layout">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <main class="cb-admin-main">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color:var(--cb-text)">
                    <i class="bi bi-camera-reels me-2" style="color:var(--cb-text)"></i>Movies
                </h2>
                <p class="text-muted-cb mb-0" style="font-size:.875rem"><?= count($movies) ?> total movies</p>
            </div>
            <button class="btn btn-cb-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#movieForm">
                <i class="bi bi-plus-circle me-1"></i><?= $editing ? 'Edit Movie' : 'Add Movie' ?>
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

        <!-- Add/Edit Form -->
        <div class="collapse <?= ($editing || $error) ? 'show' : '' ?> mb-4" id="movieForm">
            <div class="cb-card">
                <div class="card-header">
                    <i class="bi bi-<?= $editing ? 'pencil' : 'plus-circle' ?> me-2" style="color:var(--cb-text)"></i>
                    <?= $editing ? 'Edit Movie' : 'Add New Movie' ?>
                </div>
                <div class="p-4">
                    <form method="POST" action="<?= BASE_URL ?>/admin/movies.php">
                        <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?= $editing['id'] ?>"><?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="cb-label">Title *</label>
                                <input type="text" name="title" class="form-control cb-form-control"
                                       value="<?= sanitize($_POST['title'] ?? ($editing['title'] ?? '')) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="cb-label">Genre *</label>
                                <input type="text" name="genre" class="form-control cb-form-control"
                                       placeholder="e.g. Action, Sci-Fi"
                                       value="<?= sanitize($_POST['genre'] ?? ($editing['genre'] ?? '')) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Duration (min) *</label>
                                <input type="number" name="duration" class="form-control cb-form-control" min="1"
                                       value="<?= (int)($_POST['duration'] ?? ($editing['duration'] ?? 90)) ?: '' ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Rating</label>
                                <select name="rating" class="form-select cb-form-control">
                                    <?php
                                    $selectedRating = $_POST['rating'] ?? ($editing['rating'] ?? 'PG-13');
                                    foreach (['G','PG','PG-13','R','NC-17'] as $r): ?>
                                    <option value="<?= $r ?>" <?= $selectedRating === $r ? 'selected' : '' ?>><?= $r ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Status</label>
                                <select name="status" class="form-select cb-form-control">
                                    <?php $selectedStatus = $_POST['status'] ?? ($editing['status'] ?? 'now_showing'); ?>
                                    <option value="now_showing" <?= $selectedStatus === 'now_showing' ? 'selected' : '' ?>>Now Showing</option>
                                    <option value="coming_soon" <?= $selectedStatus === 'coming_soon' ? 'selected' : '' ?>>Coming Soon</option>
                                    <option value="ended"       <?= $selectedStatus === 'ended'       ? 'selected' : '' ?>>Ended</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="cb-label">Release Date</label>
                                <input type="date" name="release_date" class="form-control cb-form-control"
                                       value="<?= sanitize($_POST['release_date'] ?? ($editing['release_date'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="cb-label">Poster Filename</label>
                                <input type="text" name="poster" class="form-control cb-form-control"
                                       placeholder="e.g. movie.jpg"
                                       value="<?= sanitize($_POST['poster'] ?? ($editing['poster'] ?? 'default.jpg')) ?>">
                                <small class="text-muted-cb" style="font-size:.75rem">Upload image to <code>assets/images/</code></small>
                            </div>
                            <div class="col-12">
                                <label class="cb-label">Synopsis</label>
                                <textarea name="synopsis" class="form-control cb-form-control" rows="3"><?= sanitize($_POST['synopsis'] ?? ($editing['synopsis'] ?? '')) ?></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-cb-primary">
                                    <i class="bi bi-save me-1"></i><?= $editing ? 'Update Movie' : 'Save Movie' ?>
                                </button>
                                <?php if ($editing): ?>
                                <a href="<?= BASE_URL ?>/admin/movies.php" class="btn btn-cb-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Movies Table -->
        <div class="cb-card">
            <div class="card-header">
                <strong>All Movies</strong>
                <span class="text-muted-cb ms-1" style="font-size:.8rem">(<?= count($movies) ?>)</span>
            </div>
            <div class="table-responsive">
                <table class="table cb-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Poster</th>
                            <th>Title</th>
                            <th>Genre</th>
                            <th>Duration</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movies)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted-cb">No movies found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($movies as $m): ?>
                        <tr>
                            <td style="color:var(--cb-text-muted);font-size:.82rem"><?= $m['id'] ?></td>
                            <td>
                                <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($m['poster']) ?>"
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                                     style="width:36px;height:50px;object-fit:cover;border-radius:4px" alt="">
                            </td>
                            <td style="font-weight:600"><?= sanitize($m['title']) ?></td>
                            <td style="color:var(--cb-text-muted);font-size:.82rem"><?= sanitize($m['genre']) ?></td>
                            <td style="font-size:.85rem"><?= $m['duration'] ?> min</td>
                            <td><span class="cb-badge-rating"><?= $m['rating'] ?></span></td>
                            <td>
                                <?php if ($m['status'] === 'now_showing'): ?>
                                    <span class="status-confirmed">Now Showing</span>
                                <?php elseif ($m['status'] === 'coming_soon'): ?>
                                    <span class="status-pending">Coming Soon</span>
                                <?php else: ?>
                                    <span class="status-cancelled">Ended</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="?action=edit&id=<?= $m['id'] ?>" class="btn btn-cb-secondary btn-sm py-1 px-2">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form id="del-<?= $m['id'] ?>" method="GET" style="display:inline"
                                          action="<?= BASE_URL ?>/admin/movies.php">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button type="button" class="btn btn-sm py-1 px-2" style="background:#FEF2F2;border:1px solid #FECACA;color:var(--cb-danger)"
                                                onclick="confirmDelete('del-<?= $m['id'] ?>', 'Delete <?= addslashes(sanitize($m['title'])) ?>?')">
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
