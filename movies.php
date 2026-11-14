<?php
$page_title   = 'Movies';
$current_page = 'movies';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Filters
$status = $_GET['status'] ?? 'now_showing';
$search = trim($_GET['search'] ?? '');
$genre  = trim($_GET['genre']  ?? '');

$allowed_status = ['now_showing', 'coming_soon', 'ended'];
if (!in_array($status, $allowed_status)) $status = 'now_showing';

// Build query
$params = [];
$where  = "WHERE status = ?";
$params[] = $status;

if ($search !== '') {
    $where   .= " AND title LIKE ?";
    $params[] = '%' . $search . '%';
}
if ($genre !== '') {
    $where   .= " AND genre LIKE ?";
    $params[] = '%' . $genre . '%';
}

$movies = $db->prepare("SELECT * FROM movies $where ORDER BY created_at DESC");
$movies->execute($params);
$movies = $movies->fetchAll();

// All genres for filter
$genres = $db->query("SELECT DISTINCT genre FROM movies ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
        <h1 class="h3 text-white fw-bold mb-0">
            <i class="bi bi-camera-reels me-2 text-warning"></i>Movies
        </h1>

        <!-- Filter tabs -->
        <div class="d-flex gap-2">
            <a href="?status=now_showing<?= $search ? '&search='.urlencode($search) : '' ?>"
               class="btn btn-sm <?= $status === 'now_showing' ? 'btn-cb-primary' : 'btn-outline-secondary' ?>">Now Showing</a>
            <a href="?status=coming_soon<?= $search ? '&search='.urlencode($search) : '' ?>"
               class="btn btn-sm <?= $status === 'coming_soon' ? 'btn-cb-primary' : 'btn-outline-secondary' ?>">Coming Soon</a>
        </div>
    </div>

    <!-- Search & Genre Filter -->
    <form method="GET" class="row g-2 mb-4">
        <input type="hidden" name="status" value="<?= sanitize($status) ?>">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-cb-mid border-cb text-muted-cb"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control cb-form-control"
                       placeholder="Search movies..." value="<?= sanitize($search) ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="genre" class="form-select cb-form-control">
                <option value="">All Genres</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= sanitize($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                        <?= sanitize($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-cb-primary w-100">Filter</button>
        </div>
    </form>

    <?php if (empty($movies)): ?>
        <div class="text-center py-5">
            <i class="bi bi-camera-reels fs-1 text-muted-cb mb-3 d-block"></i>
            <p class="text-muted-cb">No movies found.</p>
            <a href="<?= BASE_URL ?>/movies.php" class="btn btn-sm btn-outline-primary">Clear Filters</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <?php foreach ($movies as $movie): ?>
            <div class="col">
                <div class="cb-movie-card">
                    <div class="poster-wrap position-relative">
                        <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($movie['poster']) ?>"
                             onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                             alt="<?= sanitize($movie['title']) ?>" loading="lazy">
                        <?php if ($movie['status'] === 'now_showing'): ?>
                            <div class="ribbon">Now Showing</div>
                        <?php elseif ($movie['status'] === 'coming_soon'): ?>
                            <div class="ribbon" style="background:#6c757d">Coming Soon</div>
                        <?php endif; ?>
                        <?php if ($movie['status'] === 'now_showing'): ?>
                        <div class="poster-overlay">
                            <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $movie['id'] ?>"
                               class="btn btn-cb-warning btn-sm w-100">
                                <i class="bi bi-play-fill me-1"></i>Book Now
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="card-title mb-0"><?= sanitize($movie['title']) ?></h6>
                            <span class="cb-badge-rating ms-1"><?= $movie['rating'] ?></span>
                        </div>
                        <span class="cb-badge-genre"><?= sanitize(explode(',', $movie['genre'])[0]) ?></span>
                        <p class="card-text mt-1">
                            <i class="bi bi-clock me-1"></i><?= $movie['duration'] ?> min
                        </p>
                        <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $movie['id'] ?>"
                           class="btn btn-sm btn-cb-primary w-100 mt-1">Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
