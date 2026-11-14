<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

// Get showtimes for the next 7 days grouped by date
$stmt2 = $db->prepare("
    SELECT * FROM showtimes
    WHERE movie_id = ? AND show_date >= CURDATE() AND show_date <= DATE_ADD(CURDATE(), INTERVAL 6 DAY)
    ORDER BY show_date ASC, show_time ASC
");
$stmt2->execute([$id]);
$allShowtimes = $stmt2->fetchAll();

// Group by date
$showtimesByDate = [];
foreach ($allShowtimes as $st) {
    $showtimesByDate[$st['show_date']][] = $st;
}

// First available date
$firstDate = $allShowtimes[0]['show_date'] ?? null;

$page_title   = sanitize($movie['title']);
$current_page = 'movies';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <!-- Back -->
    <a href="<?= BASE_URL ?>/movies.php" class="btn btn-sm btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left me-1"></i>Back to Movies
    </a>

    <div class="row g-4">
        <!-- Poster -->
        <div class="col-md-3 text-center text-md-start">
            <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($movie['poster']) ?>"
                 onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                 alt="<?= sanitize($movie['title']) ?>"
                 class="poster-detail img-fluid">
        </div>

        <!-- Info -->
        <div class="col-md-9">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="cb-badge-rating fs-6"><?= $movie['rating'] ?></span>
                <span class="cb-badge-genre"><?= sanitize($movie['genre']) ?></span>
                <?php if ($movie['status'] === 'now_showing'): ?>
                    <span class="badge bg-success">Now Showing</span>
                <?php elseif ($movie['status'] === 'coming_soon'): ?>
                    <span class="badge bg-secondary">Coming Soon</span>
                <?php endif; ?>
            </div>
            <h1 class="text-white fw-bold mb-3"><?= sanitize($movie['title']) ?></h1>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="cb-card p-3 text-center">
                        <i class="bi bi-clock text-warning d-block mb-1"></i>
                        <div class="text-white fw-semibold"><?= $movie['duration'] ?> min</div>
                        <small class="text-muted-cb">Duration</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="cb-card p-3 text-center">
                        <i class="bi bi-calendar text-warning d-block mb-1"></i>
                        <div class="text-white fw-semibold">
                            <?= $movie['release_date'] ? date('d M Y', strtotime($movie['release_date'])) : 'TBA' ?>
                        </div>
                        <small class="text-muted-cb">Release</small>
                    </div>
                </div>
            </div>

            <h5 class="text-white fw-semibold mb-2">Synopsis</h5>
            <p class="text-muted-cb" style="line-height:1.8">
                <?= nl2br(sanitize($movie['synopsis'] ?? 'No synopsis available.')) ?>
            </p>
        </div>
    </div>

    <!-- Showtimes Section -->
    <?php if ($movie['status'] === 'now_showing'): ?>
    <div class="cb-card mt-5">
        <div class="card-header">
            <i class="bi bi-calendar-week me-2 text-warning"></i>Select Showtime
        </div>
        <div class="p-4">
            <?php if (empty($showtimesByDate)): ?>
                <p class="text-muted-cb">No showtimes available at this time.</p>
            <?php else: ?>
                <!-- Date Tabs -->
                <ul class="nav nav-tabs border-cb mb-4" id="dateTabs">
                    <?php $i = 0; foreach ($showtimesByDate as $date => $times): ?>
                    <li class="nav-item">
                        <button class="nav-link <?= $i === 0 ? 'active' : '' ?> text-white"
                                data-bs-toggle="tab"
                                data-bs-target="#date-<?= str_replace('-', '', $date) ?>"
                                style="background:<?= $i === 0 ? 'var(--cb-accent)' : 'var(--cb-mid)' ?>; border-color: var(--cb-border);">
                            <?= date('D, d M', strtotime($date)) ?>
                        </button>
                    </li>
                    <?php $i++; endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php $i = 0; foreach ($showtimesByDate as $date => $times): ?>
                    <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                         id="date-<?= str_replace('-', '', $date) ?>">
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($times as $st): ?>
                            <?php if (isLoggedIn()): ?>
                                <a href="<?= BASE_URL ?>/booking/seats.php?showtime_id=<?= $st['id'] ?>"
                                   class="cb-showtime-btn text-decoration-none d-block">
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/login.php"
                                   class="cb-showtime-btn text-decoration-none d-block" title="Login to book">
                            <?php endif; ?>
                                    <div class="fw-bold text-white"><?= date('H:i', strtotime($st['show_time'])) ?></div>
                                    <div class="small text-muted-cb"><?= sanitize($st['hall']) ?></div>
                                    <div class="small text-warning"><?= formatPrice($st['price']) ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $i++; endforeach; ?>
                </div>

                <?php if (!isLoggedIn()): ?>
                <p class="text-muted-cb small mt-3">
                    <i class="bi bi-info-circle me-1"></i>
                    <a href="<?= BASE_URL ?>/login.php" class="text-warning">Login</a> to book tickets.
                </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
