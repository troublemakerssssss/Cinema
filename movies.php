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

$statusLabels = [
    'now_showing' => 'Now Showing',
    'coming_soon' => 'Coming Soon',
    'ended'       => 'Ended',
];
?>

<!-- Page Header -->
<div style="background:var(--cb-white);border-bottom:1px solid var(--cb-border)">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1" style="color:var(--cb-text)">
                    <i class="bi bi-camera-reels me-2" style="color:var(--cb-text)"></i>Movies
                </h1>
                <p class="text-muted-cb mb-0" style="font-size:.875rem">
                    Browse and book your favourite movies
                </p>
            </div>

            <!-- Status Filter Tabs -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="?status=now_showing<?= $search ? '&search='.urlencode($search) : '' ?><?= $genre ? '&genre='.urlencode($genre) : '' ?>"
                   class="btn btn-sm <?= $status === 'now_showing' ? 'btn-cb-primary' : 'btn-cb-secondary' ?>">
                    <i class="bi bi-camera-reels me-1"></i>Now Showing
                </a>
                <a href="?status=coming_soon<?= $search ? '&search='.urlencode($search) : '' ?><?= $genre ? '&genre='.urlencode($genre) : '' ?>"
                   class="btn btn-sm <?= $status === 'coming_soon' ? 'btn-cb-primary' : 'btn-cb-secondary' ?>">
                    <i class="bi bi-calendar-event me-1"></i>Coming Soon
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Search & Genre Filter Card -->
    <div class="cb-card mb-4">
        <div class="p-3">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="status" value="<?= sanitize($status) ?>">
                <div class="col-md-6">
                    <label class="cb-label"><i class="bi bi-search me-1"></i>Search Movies</label>
                    <div class="input-group">
                        <span class="input-group-text cb-ig-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control cb-form-control"
                               placeholder="Search by title..." value="<?= sanitize($search) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="cb-label"><i class="bi bi-funnel me-1"></i>Filter by Genre</label>
                    <select name="genre" class="form-select cb-form-control">
                        <option value="">All Genres</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?= sanitize($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                                <?= sanitize($g) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-cb-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    <?php if ($search || $genre): ?>
                    <a href="?status=<?= $status ?>" class="btn btn-cb-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <span style="font-size:.875rem;font-weight:600;color:var(--cb-text)">
                <?= count($movies) ?> <?= $statusLabels[$status] ?? '' ?> Movie<?= count($movies) !== 1 ? 's' : '' ?>
            </span>
            <?php if ($search || $genre): ?>
            <span class="ms-2" style="font-size:.8rem;color:var(--cb-text-muted)">
                matching "<?= sanitize($search ?: $genre) ?>"
            </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($movies)): ?>
        <div class="cb-empty">
            <div class="empty-icon"><i class="bi bi-camera-reels"></i></div>
            <h5>No Movies Found</h5>
            <p>Try adjusting your search or filter criteria.</p>
            <a href="<?= BASE_URL ?>/movies.php?status=<?= $status ?>" class="btn btn-cb-primary btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Clear Filters
            </a>
        </div>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-3">
            <?php foreach ($movies as $movie): ?>
            <div class="col">
                <div class="cb-movie-card">
                    <div class="poster-wrap position-relative">
                        <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($movie['poster']) ?>"
                             onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                             alt="<?= sanitize($movie['title']) ?>" loading="lazy">
                        <?php if ($movie['status'] === 'now_showing'): ?>
                            <div class="ribbon ribbon-showing">Showing</div>
                        <?php elseif ($movie['status'] === 'coming_soon'): ?>
                            <div class="ribbon ribbon-soon">Coming Soon</div>
                        <?php endif; ?>
                        <?php if ($movie['status'] === 'now_showing'): ?>
                        <div class="poster-overlay">
                            <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $movie['id'] ?>"
                               class="btn btn-cb-primary btn-sm w-100">
                                <i class="bi bi-ticket-perforated me-1"></i>Book Now
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title" title="<?= sanitize($movie['title']) ?>"><?= sanitize($movie['title']) ?></h6>
                        <div class="card-meta mb-2">
                            <span class="cb-badge-genre"><?= sanitize(explode(',', $movie['genre'])[0]) ?></span>
                            <span class="cb-badge-rating"><i class="bi bi-star-fill" style="font-size:.6rem"></i><?= sanitize($movie['rating']) ?></span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span style="font-size:.78rem;color:var(--cb-text-muted)">
                                <i class="bi bi-clock me-1"></i><?= $movie['duration'] ?> min
                            </span>
                            <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $movie['id'] ?>"
                               class="btn btn-cb-primary btn-sm py-1 px-2" style="font-size:.75rem">
                                Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
